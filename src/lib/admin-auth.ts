import { api } from "./api";

export const ADMIN_SESSION_KEY = "admin_session";

export interface AdminSession {
  id: string;
  email: string;
  display_name: string | null;
  isValid: boolean;
}

export function storeAdminSession(session: AdminSession): void {
  try {
    localStorage.setItem(ADMIN_SESSION_KEY, JSON.stringify(session));
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
  } catch {
    // noop
  }
}

export async function registerAdmin(
  email: string,
  password: string,
  displayName: string | null = null
): Promise<{ session: AdminSession | null; error: string | null }> {
  try {
    const data = await api.post("/api/admin/register", {
      email: email.toLowerCase().trim(),
      password,
      display_name: displayName,
    });
    if (!data.ok) return { session: null, error: "Registration failed" };
    return { session: data.session, error: null };
  } catch (err: any) {
    return { session: null, error: err.message || "Registration failed" };
  }
}

export async function authenticateAdmin(
  email: string,
  password: string
): Promise<{ session: AdminSession | null; error: string | null }> {
  try {
    const data = await api.post("/api/admin/login", {
      email: email.toLowerCase().trim(),
      password,
    });
    if (!data.ok) return { session: null, error: "Authentication failed" };
    return { session: data.session, error: null };
  } catch (err: any) {
    return { session: null, error: err.message || "Authentication failed" };
  }
}
