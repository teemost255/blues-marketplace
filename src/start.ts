import { createStart, createMiddleware } from "@tanstack/react-start";
import { redirect } from "@tanstack/react-router";

import { renderErrorPage } from "./lib/error-page";

const errorMiddleware = createMiddleware().server(async ({ next }) => {
  try {
    return await next();
  } catch (error) {
    if (error != null && typeof error === "object" && "statusCode" in error) {
      throw error;
    }
    console.error(error);
    return new Response(renderErrorPage(), {
      status: 500,
      headers: { "content-type": "text/html; charset=utf-8" },
    });
  }
});

const AUTH_TOKEN_COOKIE_NAME = "blues_marketplace_access_token";
const ADMIN_COOKIE_NAME = "blues_admin_session";

function getCookie(cookieHeader: string | null, name: string) {
  if (!cookieHeader) return null;
  return (
    cookieHeader
      .split(";")
      .map((cookie) => cookie.trim())
      .find((cookie) => cookie.startsWith(`${name}=`))
      ?.split("=")
      .slice(1)
      .join("=")
      .trim() || null
  );
}

function decodeJwtPayload(token: string): Record<string, unknown> | null {
  try {
    const parts = token.split(".");
    if (parts.length !== 3) return null;
    const base64 = parts[1].replace(/-/g, "+").replace(/_/g, "/");
    const json = Buffer.from(base64, "base64").toString("utf-8");
    return JSON.parse(json);
  } catch {
    return null;
  }
}

async function resolveUserRole(token: string): Promise<"admin" | "moderator" | "user" | null> {
  const claims = decodeJwtPayload(token);
  if (!claims?.sub) return null;

  const supabaseUrl = process.env.SUPABASE_URL;
  const supabaseKey = process.env.SUPABASE_PUBLISHABLE_KEY;
  if (!supabaseUrl || !supabaseKey) return null;

  try {
    const { createClient } = await import("@supabase/supabase-js");
    const client = createClient(supabaseUrl, supabaseKey, {
      global: { headers: { Authorization: `Bearer ${token}` } },
      auth: { persistSession: false, autoRefreshToken: false },
    });

    const userId = claims.sub as string;
    const { data: roles } = await client
      .from("user_roles")
      .select("role")
      .eq("user_id", userId);

    if (!roles?.length) return null;
    if (roles.some((r: { role: string }) => r.role === "admin")) return "admin";
    if (roles.some((r: { role: string }) => r.role === "moderator")) return "moderator";
    if (roles.some((r: { role: string }) => r.role === "user")) return "user";
    return null;
  } catch {
    return null;
  }
}

const authRedirectMiddleware = createMiddleware().server(async ({ next, request }) => {
  const url = new URL(request.url);
  const pathname = url.pathname;
  const acceptHeader = request.headers.get("accept") ?? "";
  const isHtmlRequest = acceptHeader.includes("text/html");
  const token = getCookie(request.headers.get("cookie"), AUTH_TOKEN_COOKIE_NAME);
  const adminSession = getCookie(request.headers.get("cookie"), ADMIN_COOKIE_NAME);

  // Admin auth pages: allow everyone, but redirect already-authenticated admins
  if (pathname === "/adminlogin" || pathname === "/adminregister") {
    if (adminSession) {
      throw redirect({ to: "/admin" });
    }
    if (token) {
      const role = await resolveUserRole(token);
      if (role === "admin" || role === "moderator") {
        throw redirect({ to: "/admin" });
      }
    }
    return next();
  }

  // Admin routes: allow if admin session cookie OR valid Supabase auth with admin role
  if (pathname.startsWith("/admin")) {
    if (adminSession) {
      return next();
    }
    if (!token) {
      throw redirect({ to: "/adminlogin" });
    }
    const role = await resolveUserRole(token);
    if (!role) {
      throw redirect({ to: "/adminlogin" });
    }
    if (role !== "admin" && role !== "moderator") {
      return new Response("Forbidden", { status: 403 });
    }
    return next();
  }

  if (!isHtmlRequest || pathname.startsWith("/api")) {
    return next();
  }

  const role = token ? await resolveUserRole(token) : null;
  const isAuthPage = pathname === "/login" || pathname === "/register";

  if (isAuthPage && role) {
    const destination = role === "admin" || role === "moderator" ? "/admin" : "/dashboard";
    throw redirect({ to: destination });
  }

  if (pathname.startsWith("/dashboard") && !role) {
    throw redirect({ to: "/login" });
  }

  if (pathname === "/" && role) {
    const destination = role === "admin" || role === "moderator" ? "/admin" : "/dashboard";
    throw redirect({ to: destination });
  }

  return next();
});

export const startInstance = createStart(() => ({
  requestMiddleware: [errorMiddleware, authRedirectMiddleware],
}));
