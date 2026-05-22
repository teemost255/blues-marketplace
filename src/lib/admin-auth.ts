// Legacy helpers — kept for backwards compatibility with components that imported them.
// All real authentication now flows through supabase.auth + the user_roles table.

export interface AdminSession {
  id: string;
  email: string;
  display_name: string | null;
  isValid: boolean;
}

export function clearAdminSession(): void {
  try {
    localStorage.removeItem("admin_session");
  } catch {
    /* noop */
  }
}
