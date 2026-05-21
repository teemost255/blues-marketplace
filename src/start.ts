import { createStart, createMiddleware } from "@tanstack/react-start";
import { redirect } from "@tanstack/react-router";

import { renderErrorPage } from "./lib/error-page";
import { supabaseAdmin } from "@/integrations/supabase/client.server";
import type { Database } from "@/integrations/supabase/types";

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

function getCookie(cookieHeader: string | null, name: string) {
  if (!cookieHeader) return null;
  return cookieHeader
    .split(";")
    .map((cookie) => cookie.trim())
    .find((cookie) => cookie.startsWith(`${name}=`))
    ?.split("=")
    .slice(1)
    .join("=")
    .trim() || null;
}

function normalizeRole(role: unknown): "admin" | "moderator" | "user" | null {
  if (role === "admin" || role === "moderator" || role === "user") return role;
  return null;
}

function getRoleFromClaims(claims: Record<string, unknown>): "admin" | "moderator" | "user" | null {
  return (
    normalizeRole(claims.role) ||
    normalizeRole(claims.app_role) ||
    normalizeRole(claims.user_role) ||
    normalizeRole(claims["x-hasura-role"])
  );
}

async function resolveUserRole(token: string) {
  const { data, error } = await supabaseAdmin.auth.getClaims(token);
  if (error || !data?.claims?.sub) {
    return null;
  }

  const claimRole = getRoleFromClaims(data.claims as Record<string, unknown>);
  if (claimRole) {
    return claimRole;
  }

  const userId = data.claims.sub;
  const { data: roles, error: roleError } = await supabaseAdmin
    .from("user_roles")
    .select("role")
    .eq("user_id", userId);

  if (roleError || !roles?.length) {
    return null;
  }

  if (roles.some((row: { role: string }) => row.role === "admin")) {
    return "admin";
  }

  if (roles.some((row: { role: string }) => row.role === "moderator")) {
    return "moderator";
  }

  if (roles.some((row: { role: string }) => row.role === "user")) {
    return "user";
  }

  return null;
}

const authRedirectMiddleware = createMiddleware().server(async ({ next, request }) => {
  const url = new URL(request.url);
  const pathname = url.pathname;
  const acceptHeader = request.headers.get("accept") ?? "";
  const isHtmlRequest = acceptHeader.includes("text/html");
  const token = getCookie(request.headers.get("cookie"), AUTH_TOKEN_COOKIE_NAME);
  const role = token ? await resolveUserRole(token) : null;
  const isAuthPage = pathname === "/login" || pathname === "/register";

  if (pathname.startsWith("/admin")) {
    if (!token || !role) {
      throw redirect({ to: "/login" });
    }

    if (role !== "admin" && role !== "moderator") {
      return new Response("Forbidden", { status: 403 });
    }

    return next();
  }

  if (!isHtmlRequest || pathname.startsWith("/api")) {
    return next();
  }

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
