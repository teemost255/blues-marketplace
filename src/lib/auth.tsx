import { createContext, useContext, useEffect, useState, type ReactNode } from "react";
import { api } from "./api";

export interface AuthUser {
  id: string;
  email: string | null;
  display_name: string | null;
  avatar_url: string | null;
  status: string;
  role: "admin" | "moderator" | "user";
}

interface AuthContextValue {
  user: AuthUser | null;
  role: "admin" | "moderator" | "user" | null;
  isStaff: boolean;
  loading: boolean;
  signOut: () => Promise<void>;
  refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(true);

  const loadUser = async () => {
    try {
      const data = await api.get("/api/auth/me");
      setUser(data.user ?? null);
    } catch {
      setUser(null);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadUser();
  }, []);

  const signOut = async () => {
    await api.post("/api/auth/logout");
    setUser(null);
  };

  const refreshUser = async () => {
    await loadUser();
  };

  const role = user?.role ?? null;
  const isStaff = role === "admin" || role === "moderator";

  return (
    <AuthContext.Provider value={{ user, role, isStaff, loading, signOut, refreshUser }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
