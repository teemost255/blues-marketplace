import express from "express";
import session from "express-session";
import connectPgSimple from "connect-pg-simple";
import bcrypt from "bcryptjs";
import { pool, query } from "./db.js";

const app = express();
const PgSession = connectPgSimple(session);

app.use(express.json({ limit: "10mb" }));
app.use(express.urlencoded({ extended: true }));

app.use(
  session({
    store: new PgSession({ pool, createTableIfMissing: true }),
    secret: process.env.SESSION_SECRET || "blues-marketplace-secret-key-change-in-production",
    resave: false,
    saveUninitialized: false,
    cookie: {
      secure: process.env.NODE_ENV === "production",
      httpOnly: true,
      maxAge: 7 * 24 * 60 * 60 * 1000,
      sameSite: "lax",
    },
  })
);

declare module "express-session" {
  interface SessionData {
    userId?: string;
    adminId?: string;
    adminEmail?: string;
    adminDisplayName?: string | null;
  }
}

function requireAuth(req: express.Request, res: express.Response, next: express.NextFunction) {
  if (!req.session.userId) {
    res.status(401).json({ error: "Unauthorized" });
    return;
  }
  next();
}

function requireAdmin(req: express.Request, res: express.Response, next: express.NextFunction) {
  if (!req.session.adminId) {
    res.status(401).json({ error: "Admin authentication required" });
    return;
  }
  next();
}

async function ensureUserExists(userId: string, email?: string, displayName?: string) {
  await query(
    `INSERT INTO users (id, email, display_name, status)
     VALUES ($1, $2, $3, 'active')
     ON CONFLICT (id) DO UPDATE SET
       email = COALESCE(EXCLUDED.email, users.email),
       display_name = COALESCE(EXCLUDED.display_name, users.display_name),
       updated_at = now()`,
    [userId, email || null, displayName || null]
  );
  await query(
    `INSERT INTO wallets (user_id) VALUES ($1) ON CONFLICT (user_id) DO NOTHING`,
    [userId]
  );
}

// ─── Auth ────────────────────────────────────────────────────────────────────

app.post("/api/auth/replit-login", async (req, res) => {
  try {
    const { userId, name, bio, profileImage } = req.body;
    if (!userId) {
      res.status(400).json({ error: "Missing userId" });
      return;
    }
    await ensureUserExists(userId, undefined, name);
    req.session.userId = userId;
    req.session.save();
    res.json({ ok: true, userId });
  } catch (err) {
    console.error("replit-login error:", err);
    res.status(500).json({ error: "Login failed" });
  }
});

app.post("/api/auth/logout", (req, res) => {
  req.session.destroy(() => {});
  res.json({ ok: true });
});

app.get("/api/auth/me", async (req, res) => {
  if (!req.session.userId) {
    res.json({ user: null });
    return;
  }
  try {
    const r = await query(
      `SELECT u.id, u.email, u.display_name, u.avatar_url, u.status,
              COALESCE(json_agg(ur.role) FILTER (WHERE ur.role IS NOT NULL), '[]') AS roles
       FROM users u
       LEFT JOIN user_roles ur ON ur.user_id = u.id
       WHERE u.id = $1
       GROUP BY u.id`,
      [req.session.userId]
    );
    const user = r.rows[0] || null;
    if (!user) {
      req.session.destroy(() => {});
      res.json({ user: null });
      return;
    }
    const roles: string[] = user.roles || [];
    let role: string = "user";
    if (roles.includes("admin")) role = "admin";
    else if (roles.includes("moderator")) role = "moderator";
    res.json({ user: { ...user, role } });
  } catch (err) {
    console.error("auth/me error:", err);
    res.status(500).json({ error: "Failed to get user" });
  }
});

// ─── Admin Auth ───────────────────────────────────────────────────────────────

app.post("/api/admin/login", async (req, res) => {
  try {
    const { email, password } = req.body;
    if (!email || !password) {
      res.status(400).json({ error: "Email and password required" });
      return;
    }
    const r = await query(
      `SELECT id, email, password_hash, display_name FROM admins_users WHERE email = $1`,
      [email.toLowerCase().trim()]
    );
    const admin = r.rows[0];
    if (!admin) {
      res.status(401).json({ error: "Invalid credentials" });
      return;
    }
    const valid = await bcrypt.compare(password, admin.password_hash);
    if (!valid) {
      res.status(401).json({ error: "Invalid credentials" });
      return;
    }
    req.session.adminId = admin.id;
    req.session.adminEmail = admin.email;
    req.session.adminDisplayName = admin.display_name;
    req.session.save();
    res.json({ ok: true, session: { id: admin.id, email: admin.email, display_name: admin.display_name, isValid: true } });
  } catch (err) {
    console.error("admin/login error:", err);
    res.status(500).json({ error: "Login failed" });
  }
});

app.post("/api/admin/register", async (req, res) => {
  try {
    const { email, password, display_name } = req.body;
    if (!email || !password) {
      res.status(400).json({ error: "Email and password required" });
      return;
    }
    const existing = await query(`SELECT id FROM admins_users WHERE email = $1`, [email.toLowerCase().trim()]);
    if (existing.rows.length > 0) {
      res.status(400).json({ error: "An account with this email already exists" });
      return;
    }
    const hash = await bcrypt.hash(password, 12);
    const r = await query(
      `INSERT INTO admins_users (email, password_hash, display_name) VALUES ($1, $2, $3) RETURNING id, email, display_name`,
      [email.toLowerCase().trim(), hash, display_name || null]
    );
    const admin = r.rows[0];
    req.session.adminId = admin.id;
    req.session.adminEmail = admin.email;
    req.session.adminDisplayName = admin.display_name;
    req.session.save();
    res.json({ ok: true, session: { id: admin.id, email: admin.email, display_name: admin.display_name, isValid: true } });
  } catch (err) {
    console.error("admin/register error:", err);
    res.status(500).json({ error: "Registration failed" });
  }
});

app.post("/api/admin/logout", (req, res) => {
  req.session.adminId = undefined;
  req.session.adminEmail = undefined;
  req.session.adminDisplayName = undefined;
  req.session.save();
  res.json({ ok: true });
});

app.get("/api/admin/me", (req, res) => {
  if (!req.session.adminId) {
    res.json({ session: null });
    return;
  }
  res.json({
    session: {
      id: req.session.adminId,
      email: req.session.adminEmail,
      display_name: req.session.adminDisplayName ?? null,
      isValid: true,
    },
  });
});

// ─── Listings ─────────────────────────────────────────────────────────────────

app.get("/api/listings", async (req, res) => {
  try {
    const { q, category, sort, page = "0", limit = "9", active_only = "true" } = req.query as Record<string, string>;
    const pageNum = parseInt(page, 10) || 0;
    const pageSize = parseInt(limit, 10) || 9;
    const offset = pageNum * pageSize;

    let conditions = ["true"];
    const params: any[] = [];

    if (active_only !== "false") {
      conditions.push("is_active = true");
    }
    if (q) {
      params.push(`%${q}%`);
      conditions.push(`title ILIKE $${params.length}`);
    }
    if (category && category !== "all") {
      params.push(category);
      conditions.push(`category = $${params.length}`);
    }

    const where = conditions.join(" AND ");
    let orderBy = "created_at DESC";
    if (sort === "price_asc") orderBy = "price ASC";
    else if (sort === "price_desc") orderBy = "price DESC";

    const countResult = await query(`SELECT COUNT(*) FROM listings WHERE ${where}`, params);
    const count = parseInt(countResult.rows[0].count, 10);

    params.push(pageSize, offset);
    const dataResult = await query(
      `SELECT * FROM listings WHERE ${where} ORDER BY ${orderBy} LIMIT $${params.length - 1} OFFSET $${params.length}`,
      params
    );
    res.json({ rows: dataResult.rows, count });
  } catch (err) {
    console.error("GET /api/listings error:", err);
    res.status(500).json({ error: "Failed to fetch listings" });
  }
});

app.get("/api/listings/:id", async (req, res) => {
  try {
    const r = await query(`SELECT * FROM listings WHERE id = $1`, [req.params.id]);
    res.json(r.rows[0] || null);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch listing" });
  }
});

app.post("/api/listings", requireAdmin, async (req, res) => {
  try {
    const { title, description, price, category, image_url, stock, is_active } = req.body;
    const r = await query(
      `INSERT INTO listings (title, description, price, category, image_url, stock, is_active)
       VALUES ($1,$2,$3,$4,$5,$6,$7) RETURNING *`,
      [title, description, price, category, image_url, stock ?? 1, is_active ?? true]
    );
    res.json(r.rows[0]);
  } catch (err) {
    console.error("POST /api/listings error:", err);
    res.status(500).json({ error: "Failed to create listing" });
  }
});

app.put("/api/listings/:id", requireAdmin, async (req, res) => {
  try {
    const { title, description, price, category, image_url, stock, is_active } = req.body;
    const r = await query(
      `UPDATE listings SET title=$1, description=$2, price=$3, category=$4, image_url=$5, stock=$6, is_active=$7, updated_at=now()
       WHERE id=$8 RETURNING *`,
      [title, description, price, category, image_url, stock, is_active, req.params.id]
    );
    res.json(r.rows[0] || null);
  } catch (err) {
    res.status(500).json({ error: "Failed to update listing" });
  }
});

app.delete("/api/listings/:id", requireAdmin, async (req, res) => {
  try {
    await query(`DELETE FROM listings WHERE id = $1`, [req.params.id]);
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to delete listing" });
  }
});

// ─── Categories ───────────────────────────────────────────────────────────────

app.get("/api/categories", async (_req, res) => {
  try {
    const r = await query(`SELECT name FROM listing_categories ORDER BY name`);
    res.json(r.rows.map((row: any) => row.name));
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch categories" });
  }
});

app.post("/api/categories", requireAdmin, async (req, res) => {
  try {
    const { name } = req.body;
    await query(`INSERT INTO listing_categories (name, created_by) VALUES ($1, $2) ON CONFLICT (name) DO NOTHING`, [name, req.session.adminId]);
    await query(
      `INSERT INTO admin_audit_log (actor_id, action, target_type, target_id, meta) VALUES ($1,$2,$3,$4,$5)`,
      [req.session.adminId, "create_category", "listing_categories", name, JSON.stringify({ name })]
    );
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to create category" });
  }
});

// ─── Wallet ───────────────────────────────────────────────────────────────────

app.get("/api/wallet", requireAuth, async (req, res) => {
  try {
    const r = await query(`SELECT balance FROM wallets WHERE user_id = $1`, [req.session.userId]);
    res.json(r.rows[0] || { balance: 0 });
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch wallet" });
  }
});

app.get("/api/wallet/transactions", requireAuth, async (req, res) => {
  try {
    const r = await query(
      `SELECT * FROM wallet_transactions WHERE user_id = $1 ORDER BY created_at DESC LIMIT 50`,
      [req.session.userId]
    );
    res.json(r.rows);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch transactions" });
  }
});

app.post("/api/wallet/deposit", requireAuth, async (req, res) => {
  try {
    const { amount } = req.body;
    const n = Number(amount);
    if (!n || n < 100) {
      res.status(400).json({ error: "Minimum ₦100" });
      return;
    }
    const client = await pool.connect();
    try {
      await client.query("BEGIN");
      await client.query(
        `INSERT INTO wallets (user_id, balance) VALUES ($1, $2)
         ON CONFLICT (user_id) DO UPDATE SET balance = wallets.balance + $2, updated_at = now()`,
        [req.session.userId, n]
      );
      await client.query(
        `INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES ($1,$2,$3,$4)`,
        [req.session.userId, n, "deposit", "Wallet top-up (test mode)"]
      );
      await client.query("COMMIT");
    } catch (e) {
      await client.query("ROLLBACK");
      throw e;
    } finally {
      client.release();
    }
    res.json({ ok: true });
  } catch (err) {
    console.error("wallet/deposit error:", err);
    res.status(500).json({ error: "Deposit failed" });
  }
});

// ─── Checkout ─────────────────────────────────────────────────────────────────

app.post("/api/checkout/:listingId", requireAuth, async (req, res) => {
  const client = await pool.connect();
  try {
    await client.query("BEGIN");
    const listingResult = await client.query(
      `SELECT id, price, stock, title FROM listings WHERE id = $1 AND is_active = true FOR UPDATE`,
      [req.params.listingId]
    );
    const listing = listingResult.rows[0];
    if (!listing) {
      await client.query("ROLLBACK");
      res.status(404).json({ error: "Listing not found or unavailable" });
      return;
    }
    if (listing.stock <= 0) {
      await client.query("ROLLBACK");
      res.status(400).json({ error: "Out of stock" });
      return;
    }
    const walletResult = await client.query(
      `SELECT balance FROM wallets WHERE user_id = $1 FOR UPDATE`,
      [req.session.userId]
    );
    const balance = Number(walletResult.rows[0]?.balance || 0);
    if (balance < Number(listing.price)) {
      await client.query("ROLLBACK");
      res.status(400).json({ error: "Insufficient wallet balance" });
      return;
    }
    await client.query(
      `UPDATE wallets SET balance = balance - $1, updated_at = now() WHERE user_id = $2`,
      [listing.price, req.session.userId]
    );
    await client.query(
      `INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES ($1,$2,$3,$4)`,
      [req.session.userId, -listing.price, "purchase", `Purchase: ${listing.title}`]
    );
    await client.query(
      `UPDATE listings SET stock = stock - 1, updated_at = now() WHERE id = $1`,
      [listing.id]
    );
    const purchaseResult = await client.query(
      `INSERT INTO purchases (user_id, listing_id, amount, status) VALUES ($1,$2,$3,'completed') RETURNING id`,
      [req.session.userId, listing.id, listing.price]
    );
    await client.query("COMMIT");
    res.json({ ok: true, purchaseId: purchaseResult.rows[0].id });
  } catch (err) {
    await client.query("ROLLBACK");
    console.error("checkout error:", err);
    res.status(500).json({ error: "Checkout failed" });
  } finally {
    client.release();
  }
});

// ─── Purchases ────────────────────────────────────────────────────────────────

app.get("/api/purchases/my", requireAuth, async (req, res) => {
  try {
    const r = await query(
      `SELECT p.id, p.amount, p.status, p.created_at, p.listing_id,
              l.title AS listing_title, l.image_url AS listing_image_url, l.category AS listing_category
       FROM purchases p
       LEFT JOIN listings l ON l.id = p.listing_id
       WHERE p.user_id = $1
       ORDER BY p.created_at DESC`,
      [req.session.userId]
    );
    const rows = r.rows.map((p: any) => ({
      ...p,
      listing: p.listing_id ? { id: p.listing_id, title: p.listing_title, image_url: p.listing_image_url, category: p.listing_category } : null,
    }));
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch purchases" });
  }
});

app.get("/api/purchases/stats", requireAuth, async (req, res) => {
  try {
    const r = await query(
      `SELECT amount, status, created_at, listing_id FROM purchases WHERE user_id = $1 ORDER BY created_at DESC`,
      [req.session.userId]
    );
    const list = r.rows;
    const total = list.reduce((s: number, p: any) => s + Number(p.amount), 0);
    const completed = list.filter((p: any) => p.status === "completed").length;
    const pending = list.filter((p: any) => p.status === "pending").length;
    res.json({ count: list.length, total, completed, pending, recent: list.slice(0, 5) });
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch stats" });
  }
});

// ─── Wishlist ─────────────────────────────────────────────────────────────────

app.get("/api/wishlist", requireAuth, async (req, res) => {
  try {
    const r = await query(
      `SELECT w.id, w.listing_id, w.created_at,
              l.id AS l_id, l.title, l.price, l.category, l.image_url, l.is_active
       FROM wishlists w
       LEFT JOIN listings l ON l.id = w.listing_id
       WHERE w.user_id = $1
       ORDER BY w.created_at DESC`,
      [req.session.userId]
    );
    const rows = r.rows.map((x: any) => ({
      id: x.id,
      listing_id: x.listing_id,
      created_at: x.created_at,
      listing: x.l_id ? { id: x.l_id, title: x.title, price: x.price, category: x.category, image_url: x.image_url, is_active: x.is_active } : null,
    }));
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch wishlist" });
  }
});

app.get("/api/wishlist/:listingId", requireAuth, async (req, res) => {
  try {
    const r = await query(
      `SELECT id FROM wishlists WHERE user_id = $1 AND listing_id = $2`,
      [req.session.userId, req.params.listingId]
    );
    res.json(r.rows[0] || null);
  } catch (err) {
    res.status(500).json({ error: "Failed to check wishlist" });
  }
});

app.post("/api/wishlist/:listingId", requireAuth, async (req, res) => {
  try {
    await query(
      `INSERT INTO wishlists (user_id, listing_id) VALUES ($1,$2) ON CONFLICT DO NOTHING`,
      [req.session.userId, req.params.listingId]
    );
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to add to wishlist" });
  }
});

app.delete("/api/wishlist/:id", requireAuth, async (req, res) => {
  try {
    await query(`DELETE FROM wishlists WHERE id = $1 AND user_id = $2`, [req.params.id, req.session.userId]);
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to remove from wishlist" });
  }
});

// ─── Profile ──────────────────────────────────────────────────────────────────

app.get("/api/profile", requireAuth, async (req, res) => {
  try {
    const r = await query(
      `SELECT id, email, display_name, username, phone, country, bio, avatar_url FROM users WHERE id = $1`,
      [req.session.userId]
    );
    res.json(r.rows[0] || null);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch profile" });
  }
});

app.put("/api/profile", requireAuth, async (req, res) => {
  try {
    const { display_name, username, phone, country, bio, avatar_url } = req.body;
    await query(
      `UPDATE users SET display_name=$1, username=$2, phone=$3, country=$4, bio=$5, avatar_url=$6, updated_at=now() WHERE id=$7`,
      [display_name, username, phone, country, bio, avatar_url, req.session.userId]
    );
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to update profile" });
  }
});

// ─── Notifications ────────────────────────────────────────────────────────────

app.get("/api/notifications", requireAuth, async (req, res) => {
  try {
    const r = await query(
      `SELECT * FROM notifications WHERE user_id = $1 ORDER BY created_at DESC LIMIT 100`,
      [req.session.userId]
    );
    res.json(r.rows);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch notifications" });
  }
});

app.get("/api/notifications/unread-count", requireAuth, async (req, res) => {
  try {
    const r = await query(
      `SELECT COUNT(*) FROM notifications WHERE user_id = $1 AND read = false`,
      [req.session.userId]
    );
    res.json({ count: parseInt(r.rows[0].count, 10) });
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch notification count" });
  }
});

app.put("/api/notifications/:id/read", requireAuth, async (req, res) => {
  try {
    await query(`UPDATE notifications SET read = true WHERE id = $1 AND user_id = $2`, [req.params.id, req.session.userId]);
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to mark notification read" });
  }
});

app.put("/api/notifications/read-all", requireAuth, async (req, res) => {
  try {
    await query(`UPDATE notifications SET read = true WHERE user_id = $1 AND read = false`, [req.session.userId]);
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to mark all read" });
  }
});

// ─── Support Tickets ──────────────────────────────────────────────────────────

app.get("/api/tickets/my", requireAuth, async (req, res) => {
  try {
    const r = await query(
      `SELECT * FROM support_tickets WHERE user_id = $1 ORDER BY created_at DESC`,
      [req.session.userId]
    );
    res.json(r.rows);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch tickets" });
  }
});

app.post("/api/tickets", requireAuth, async (req, res) => {
  try {
    const { subject, message, priority } = req.body;
    const r = await query(
      `INSERT INTO support_tickets (user_id, subject, message, priority) VALUES ($1,$2,$3,$4) RETURNING *`,
      [req.session.userId, subject, message, priority || "normal"]
    );
    res.json(r.rows[0]);
  } catch (err) {
    res.status(500).json({ error: "Failed to submit ticket" });
  }
});

// ─── Site Settings ────────────────────────────────────────────────────────────

app.get("/api/settings", async (_req, res) => {
  try {
    const r = await query(`SELECT key, value FROM site_settings`);
    const map: Record<string, any> = {};
    r.rows.forEach((row: any) => { map[row.key] = row.value; });
    res.json(map);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch settings" });
  }
});

app.get("/api/settings/:key", async (req, res) => {
  try {
    const r = await query(`SELECT value FROM site_settings WHERE key = $1`, [req.params.key]);
    res.json((r.rows[0]?.value) ?? null);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch setting" });
  }
});

app.put("/api/settings/:key", requireAdmin, async (req, res) => {
  try {
    const { value } = req.body;
    await query(
      `INSERT INTO site_settings (key, value, updated_at, updated_by) VALUES ($1,$2,now(),$3)
       ON CONFLICT (key) DO UPDATE SET value=$2, updated_at=now(), updated_by=$3`,
      [req.params.key, JSON.stringify(value), req.session.adminId]
    );
    await query(
      `INSERT INTO admin_audit_log (actor_id, action, target_type, target_id, meta) VALUES ($1,$2,$3,$4,$5)`,
      [req.session.adminId, "settings_update", "site_settings", req.params.key, JSON.stringify(value)]
    );
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to save setting" });
  }
});

// ─── Admin: Users ─────────────────────────────────────────────────────────────

app.get("/api/admin/users", requireAdmin, async (_req, res) => {
  try {
    const r = await query(
      `SELECT u.id, u.display_name, u.created_at, u.status, u.is_verified, u.suspension_reason,
              COALESCE(json_agg(ur.role) FILTER (WHERE ur.role IS NOT NULL), '[]') AS roles
       FROM users u
       LEFT JOIN user_roles ur ON ur.user_id = u.id
       GROUP BY u.id
       ORDER BY u.created_at DESC`
    );
    res.json(r.rows);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch users" });
  }
});

app.put("/api/admin/users/:id/role", requireAdmin, async (req, res) => {
  try {
    const { role } = req.body;
    await query(`DELETE FROM user_roles WHERE user_id = $1 AND role IN ('admin','moderator')`, [req.params.id]);
    if (role && role !== "user") {
      await query(`INSERT INTO user_roles (user_id, role) VALUES ($1,$2) ON CONFLICT DO NOTHING`, [req.params.id, role]);
    }
    await query(
      `INSERT INTO admin_audit_log (actor_id, action, target_type, target_id, meta) VALUES ($1,$2,$3,$4,$5)`,
      [req.session.adminId, "role_set", "user", req.params.id, JSON.stringify({ role })]
    );
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to update role" });
  }
});

app.put("/api/admin/users/:id/verify", requireAdmin, async (req, res) => {
  try {
    const { is_verified } = req.body;
    await query(`UPDATE users SET is_verified = $1, updated_at = now() WHERE id = $2`, [is_verified, req.params.id]);
    await query(
      `INSERT INTO admin_audit_log (actor_id, action, target_type, target_id, meta) VALUES ($1,$2,$3,$4,$5)`,
      [req.session.adminId, is_verified ? "verify" : "unverify", "user", req.params.id, JSON.stringify({})]
    );
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to update verification" });
  }
});

app.put("/api/admin/users/:id/status", requireAdmin, async (req, res) => {
  try {
    const { status, suspension_reason } = req.body;
    await query(
      `UPDATE users SET status=$1, suspension_reason=$2, updated_at=now() WHERE id=$3`,
      [status, status === "active" ? null : suspension_reason, req.params.id]
    );
    await query(
      `INSERT INTO admin_audit_log (actor_id, action, target_type, target_id, meta) VALUES ($1,$2,$3,$4,$5)`,
      [req.session.adminId, `status_${status}`, "user", req.params.id, JSON.stringify({ suspension_reason })]
    );
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to update status" });
  }
});

app.post("/api/admin/audit", requireAdmin, async (req, res) => {
  try {
    const { action, target_type, target_id, meta } = req.body;
    await query(
      `INSERT INTO admin_audit_log (actor_id, action, target_type, target_id, meta) VALUES ($1,$2,$3,$4,$5)`,
      [req.session.adminId, action, target_type, target_id, meta ? JSON.stringify(meta) : null]
    );
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to log audit" });
  }
});

// ─── Admin: Transactions ──────────────────────────────────────────────────────

app.get("/api/admin/transactions", requireAdmin, async (_req, res) => {
  try {
    const r = await query(
      `SELECT p.id, p.amount, p.status, p.created_at, p.user_id, l.title AS listing_title
       FROM purchases p
       LEFT JOIN listings l ON l.id = p.listing_id
       ORDER BY p.created_at DESC
       LIMIT 100`
    );
    const rows = r.rows.map((p: any) => ({
      ...p,
      listing: p.listing_title ? { title: p.listing_title } : null,
    }));
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch transactions" });
  }
});

// ─── Admin: Audit Log ─────────────────────────────────────────────────────────

app.get("/api/admin/audit", requireAdmin, async (_req, res) => {
  try {
    const r = await query(
      `SELECT id, actor_id, action, target_type, target_id, meta, created_at
       FROM admin_audit_log
       ORDER BY created_at DESC
       LIMIT 200`
    );
    res.json(r.rows);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch audit log" });
  }
});

// ─── Admin: Stats ─────────────────────────────────────────────────────────────

app.get("/api/admin/stats", requireAdmin, async (_req, res) => {
  try {
    const [users, listings, purchases] = await Promise.all([
      query(`SELECT COUNT(*) FROM users`),
      query(`SELECT COUNT(*) FROM listings`),
      query(`SELECT amount, status FROM purchases`),
    ]);
    const purchaseRows = purchases.rows;
    const revenue = purchaseRows.filter((p: any) => p.status === "completed").reduce((s: number, p: any) => s + Number(p.amount), 0);
    res.json({
      users: parseInt(users.rows[0].count, 10),
      listings: parseInt(listings.rows[0].count, 10),
      purchases: purchaseRows.length,
      revenue,
    });
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch stats" });
  }
});

// ─── Admin: Tickets ───────────────────────────────────────────────────────────

app.get("/api/admin/tickets", requireAdmin, async (_req, res) => {
  try {
    const r = await query(`SELECT * FROM support_tickets ORDER BY created_at DESC LIMIT 200`);
    res.json(r.rows);
  } catch (err) {
    res.status(500).json({ error: "Failed to fetch tickets" });
  }
});

app.put("/api/admin/tickets/:id", requireAdmin, async (req, res) => {
  try {
    const { admin_reply, status } = req.body;
    const r = await query(
      `UPDATE support_tickets SET admin_reply=COALESCE($1, admin_reply), status=COALESCE($2, status), updated_at=now()
       WHERE id=$3 RETURNING user_id, subject`,
      [admin_reply ?? null, status ?? null, req.params.id]
    );
    const ticket = r.rows[0];
    if (admin_reply && ticket) {
      await query(
        `INSERT INTO notifications (user_id, title, body, type, link) VALUES ($1,$2,$3,$4,$5)`,
        [ticket.user_id, "Support reply on your ticket", ticket.subject, "info", "/dashboard/support"]
      );
    }
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: "Failed to update ticket" });
  }
});

// ─── Start ────────────────────────────────────────────────────────────────────

const PORT = process.env.API_PORT ? parseInt(process.env.API_PORT) : 3001;

export async function startApiServer() {
  return new Promise<void>((resolve) => {
    app.listen(PORT, "0.0.0.0", () => {
      console.log(`[API] Server listening on port ${PORT}`);
      resolve();
    });
  });
}

export default app;
