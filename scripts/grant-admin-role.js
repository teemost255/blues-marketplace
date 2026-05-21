import { createClient } from "@supabase/supabase-js";

const supabaseUrl = process.env.SUPABASE_URL;
const supabaseServiceRoleKey = process.env.SUPABASE_SERVICE_ROLE_KEY;
const email = process.argv[2];

if (!supabaseUrl || !supabaseServiceRoleKey) {
  console.error("Missing SUPABASE_URL or SUPABASE_SERVICE_ROLE_KEY environment variables.");
  process.exit(1);
}

if (!email) {
  console.error("Usage: npm run grant:admin -- <email>");
  process.exit(1);
}

const supabase = createClient(supabaseUrl, supabaseServiceRoleKey, {
  auth: { persistSession: false },
});

const main = async () => {
  const { data: user, error: userError } = await supabase.auth.admin.getUserByEmail(email);

  if (userError) {
    console.error("Failed to find user:", userError.message);
    process.exit(1);
  }

  if (!user) {
    console.error(`No user found with email: ${email}`);
    process.exit(1);
  }

  const userId = user.id;

  const { error: profileError } = await supabase
    .from("profiles")
    .upsert({ id: userId, display_name: email.split("@")[0], avatar_url: null }, { onConflict: ["id"] });

  if (profileError) {
    console.error("Failed to upsert profile:", profileError.message);
    process.exit(1);
  }

  const { error: roleError } = await supabase
    .from("user_roles")
    .upsert({ user_id: userId, role: "admin" }, { onConflict: ["user_id", "role"] });

  if (roleError) {
    console.error("Failed to grant admin role:", roleError.message);
    process.exit(1);
  }

  console.log(`Granted admin role to ${email} (${userId}).`);
};

main().catch((error) => {
  console.error("Unexpected error:", error);
  process.exit(1);
});
