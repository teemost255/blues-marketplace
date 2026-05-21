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

async function resolveSessionDestination(token: string) {
  const { data, error } = await supabaseAdmin.auth.getClaims(token);
  if (error || !data?.claims?.sub) {
    return null;
  }

  const userId = data.claims.sub;
  const { data: roles, error: roleError } = await supabaseAdmin
    .from("user_roles")
    .select("role")
    .eq("user_id", userId);

  if (roleError || !roles?.length) {
    return null;
  }

  const hasStaffRole = roles.some(
    (row: { role: string }) => row.role === "admin" || row.role === "moderator",
  );

  if (hasStaffRole) {
    return "/admin";
  }

  const hasUserRole = roles.some((row: { role: string }) => row.role === "user");
  if (hasUserRole) {
    return "/dashboard";
  }

  return null;
}

const authRedirectMiddleware = createMiddleware().server(async ({ next, request }) => {
  const url = new URL(request.url);
  const pathname = url.pathname;
  const acceptHeader = request.headers.get("accept") ?? "";
  const isHtmlRequest = acceptHeader.includes("text/html");

  if (!isHtmlRequest || pathname.startsWith("/api")) {
    return next();
  }

  const token = getCookie(request.headers.get("cookie"), AUTH_TOKEN_COOKIE_NAME);
  const destination = token ? await resolveSessionDestination(token) : null;
  const isAuthPage = pathname === "/login" || pathname === "/register";

  if (destination) {
    if (isAuthPage || pathname === "/") {
      throw redirect({ to: destination });
    }

    if (pathname.startsWith("/admin") && destination === "/dashboard") {
      throw redirect({ to: "/dashboard" });
    }

    if (pathname.startsWith("/dashboard") && destination === "/admin") {
      throw redirect({ to: "/admin" });
    }

    return next();
  }

  if (pathname.startsWith("/admin") || pathname.startsWith("/dashboard")) {
    throw redirect({ to: "/login" });
  }

  return next();
});

export const startInstance = createStart(() => ({
  requestMiddleware: [errorMiddleware, authRedirectMiddleware],
}));
