import { supabase } from "@/integrations/supabase/client";

export const ADMIN_SESSION_KEY = "admin_session";
export const ADMIN_COOKIE_NAME = "blues_admin_session";

export interface AdminSession {
  id: string;
  email: string;
  display_name: string | null;
  isValid: boolean;
}

export function storeAdminSession(session: AdminSession): void {
  try {
    localStorage.setItem(ADMIN_SESSION_KEY, JSON.stringify(session));
    const maxAge = 60 * 60 * 24;
    const secure = window.location.protocol === "https:" ? "; Secure" : "";
    document.cookie = `${ADMIN_COOKIE_NAME}=${encodeURIComponent(session.id)}; path=/; max-age=${maxAge}; SameSite=Lax${secure}`;
  } catch {
    // noop
  }
}

export function getAdminSession(): AdminSession | null {
  try {
    const raw = localStorage.getItem(ADMIN_SESSION_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw) as AdminSession;
    return parsed?.isValid ? parsed : null;
  } catch {
    return null;
  }
}

export function clearAdminSession(): void {
  try {
    localStorage.removeItem(ADMIN_SESSION_KEY);
    document.cookie = `${ADMIN_COOKIE_NAME}=; path=/; max-age=0; SameSite=Lax`;
  } catch {
    // noop
  }
}

export async function authenticateAdmin(
  email: string,
  password: string
): Promise<{ session: AdminSession | null; error: string | null }> {
  try {
    const { data, error } = await supabase.rpc("verify_admin_password", {
      p_email: email.toLowerCase().trim(),
      password,
    });

    if (error) {
      console.error("verify_admin_password error:", error);
      return { session: null, error: "Authentication failed" };
    }

    const rows = data as Array<{
      id: string;
      display_name: string | null;
      email: string;
      is_valid: boolean;
    }> | null;

    const admin = rows?.[0];
    if (!admin || !admin.is_valid) {
      return { session: null, error: "Invalid credentials. Only admin accounts can sign in here." };
    }

    return {
      session: {
        id: admin.id,
        email: admin.email,
        display_name: admin.display_name,
        isValid: true,
      },
      error: null,
    };
  } catch (err) {
    console.error("authenticateAdmin error:", err);
    return { session: null, error: "An unexpected error occurred" };
  }
}
