import { supabase } from "@/integrations/supabase/client";

export interface AdminCredentials {
  email: string;
  password: string;
}

export interface AdminSession {
  id: string;
  email: string;
  display_name: string | null;
  isValid: boolean;
}

export interface AdminLoginResponse {
  success: boolean;
  admin?: AdminSession;
  error?: string;
}

/**
 * Authenticate admin user against admins_users table
 */
export async function authenticateAdmin(
  credentials: AdminCredentials
): Promise<AdminLoginResponse> {
  try {
    const response = await fetch("/api/admin/sync-auth", {
      method: "POST",
      headers: {
        "content-type": "application/json",
      },
      body: JSON.stringify({
        email: credentials.email.toLowerCase(),
        password: credentials.password,
      }),
    });

    const result = await response.json();
    if (!response.ok || !result.success) {
      return {
        success: false,
        error: result.error || "Authentication failed. Please check your credentials.",
      };
    }

    const { data, error } = await supabase.auth.signInWithPassword({
      email: credentials.email.toLowerCase(),
      password: credentials.password,
    });

    if (error) {
      console.error("Admin supabase sign-in error:", error);
      return {
        success: false,
        error: error.message,
      };
    }

    if (!data?.user) {
      return {
        success: false,
        error: "Authentication failed. No user session was created.",
      };
    }

    return {
      success: true,
      admin: {
        id: data.user.id,
        email: data.user.email ?? credentials.email.toLowerCase(),
        display_name: data.user.user_metadata?.display_name ?? null,
        isValid: true,
      },
    };
  } catch (err) {
    console.error("Admin authentication exception:", err);
    return {
      success: false,
      error: "An error occurred during authentication.",
    };
  }
}

/**
 * Check if an email is registered as an admin
 */
export async function checkIsAdminEmail(email: string): Promise<boolean> {
  try {
    const { data, error } = await supabase.rpc("is_admin_email", {
      email: email.toLowerCase(),
    });

    if (error) {
      console.error("Error checking admin email:", error);
      return false;
    }

    return data === true;
  } catch (err) {
    console.error("Exception checking admin email:", err);
    return false;
  }
}

/**
 * Store admin session in localStorage
 */
export function storeAdminSession(admin: AdminSession): void {
  try {
    localStorage.setItem("admin_session", JSON.stringify(admin));
  } catch (err) {
    console.error("Failed to store admin session:", err);
  }
}

/**
 * Retrieve admin session from localStorage
 */
export function getAdminSession(): AdminSession | null {
  try {
    const stored = localStorage.getItem("admin_session");
    if (!stored) return null;
    return JSON.parse(stored) as AdminSession;
  } catch (err) {
    console.error("Failed to retrieve admin session:", err);
    return null;
  }
}

/**
 * Clear admin session from localStorage
 */
export function clearAdminSession(): void {
  try {
    localStorage.removeItem("admin_session");
  } catch (err) {
    console.error("Failed to clear admin session:", err);
  }
}
