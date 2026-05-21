import { createContext, useContext, useEffect, useState, type ReactNode } from "react";
import type { Session, User } from "@supabase/supabase-js";
import { supabase } from "@/integrations/supabase/client";

const AUTH_TOKEN_COOKIE_NAME = "blues_marketplace_access_token";

function setAccessTokenCookie(token: string | null, expiresAt?: number) {
  if (typeof document === "undefined") return;

  if (!token) {
    document.cookie = `${AUTH_TOKEN_COOKIE_NAME}=; path=/; max-age=0; SameSite=Lax; ${window.location.protocol === "https:" ? "Secure;" : ""}`;
    return;
  }

  const maxAge = expiresAt
    ? Math.max(0, expiresAt - Math.floor(Date.now() / 1000))
    : 60 * 60 * 24;

  document.cookie = `${AUTH_TOKEN_COOKIE_NAME}=${encodeURIComponent(token)}; path=/; max-age=${Math.floor(
    maxAge,
  )}; SameSite=Lax; ${window.location.protocol === "https:" ? "Secure;" : ""}`;
}

function clearAccessTokenCookie() {
  setAccessTokenCookie(null);
}

interface AuthContextValue {
  user: User | null;
  session: Session | null;
  role: "admin" | "moderator" | "user" | null;
  isStaff: boolean;
  loading: boolean;
  signOut: () => Promise<void>;
  refreshRole: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [session, setSession] = useState<Session | null>(null);
  const [user, setUser] = useState<User | null>(null);
  const [role, setRole] = useState<"admin" | "moderator" | "user" | null>(null);
  const [loading, setLoading] = useState(true);

  const loadRole = async (uid: string) => {
    setLoading(true);

    try {
      const { data } = await supabase
        .from("user_roles")
        .select("role")
        .eq("user_id", uid);
      if (data?.some((r) => r.role === "admin")) setRole("admin");
      else if (data?.some((r) => r.role === "moderator")) setRole("moderator");
      else setRole("user");
    } catch (error) {
      console.error("Failed to load user role", error);
      setRole("user");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const { data: { subscription } } = supabase.auth.onAuthStateChange((_event, s) => {
      setSession(s);
      setUser(s?.user ?? null);
      setAccessTokenCookie(s?.access_token ?? null, s?.expires_at);
      if (s?.user) {
        loadRole(s.user.id);
      } else {
        setRole(null);
        setLoading(false);
      }
    });

    supabase.auth.getSession().then(({ data: { session: s } }) => {
      setSession(s);
      setUser(s?.user ?? null);
      setAccessTokenCookie(s?.access_token ?? null, s?.expires_at);
      if (s?.user) {
        loadRole(s.user.id);
      } else {
        setLoading(false);
      }
    });

    return () => subscription.unsubscribe();
  }, []);

  const signOut = async () => {
    await supabase.auth.signOut();
    clearAccessTokenCookie();
  };

  const refreshRole = async () => {
    if (user) await loadRole(user.id);
  };

  const isStaff = role === "admin" || role === "moderator";
  return (
    <AuthContext.Provider value={{ user, session, role, isStaff, loading, signOut, refreshRole }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
