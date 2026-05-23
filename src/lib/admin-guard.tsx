import { useEffect, type ReactNode } from "react";
import { useNavigate } from "@tanstack/react-router";
import { toast } from "sonner";
import { useAuth } from "@/lib/auth";
import { clearAdminSession, getAdminSession } from "@/lib/admin-auth";

export function AdminGuard({ children }: { children: ReactNode }) {
  const { user, role, loading } = useAuth();
  const navigate = useNavigate();
  const adminSession = typeof window !== "undefined" ? getAdminSession() : null;

  const isStaff =
    role === "admin" ||
    role === "moderator" ||
    adminSession?.isValid === true;

  useEffect(() => {
    if (loading) return;
    if (!isStaff && !adminSession) {
      navigate({ to: "/adminlogin" });
    }
  }, [loading, user, isStaff, adminSession, navigate]);

  if (loading) {
    return <div className="grid min-h-screen place-items-center text-muted-foreground">Loading…</div>;
  }

  if (!isStaff) {
    return (
      <div className="grid min-h-screen place-items-center px-4 text-center">
        <div className="max-w-md rounded-3xl border border-destructive/30 bg-destructive/10 p-10 shadow-sm">
          <h1 className="text-2xl font-semibold text-foreground">Staff access required</h1>
          <p className="mt-3 text-sm text-muted-foreground">
            Sign in with an admin account to view this section.
          </p>
          <button
            type="button"
            className="mt-6 inline-flex items-center justify-center rounded-full bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
            onClick={() => navigate({ to: "/adminlogin" })}
          >
            Sign in
          </button>
        </div>
      </div>
    );
  }

  return <>{children}</>;
}

export function AdminLoginGuard({ children }: { children: ReactNode }) {
  const { user, role, loading } = useAuth();
  const navigate = useNavigate();
  const adminSession = typeof window !== "undefined" ? getAdminSession() : null;

  const isAdminAuthenticated =
    role === "admin" ||
    role === "moderator" ||
    adminSession?.isValid === true;

  useEffect(() => {
    if (!loading) {
      if (isAdminAuthenticated) {
        navigate({ to: "/admin" });
      } else if (user && role === "user") {
        navigate({ to: "/dashboard" });
      }
    }
  }, [loading, user, role, isAdminAuthenticated, navigate]);

  if (loading) {
    return <div className="grid min-h-screen place-items-center text-muted-foreground">Loading…</div>;
  }

  return <>{children}</>;
}

export function useAdminPermissions() {
  const { role } = useAuth();
  const adminSession = typeof window !== "undefined" ? getAdminSession() : null;

  const isAdmin = role === "admin" || adminSession?.isValid === true;
  const isModerator = role === "moderator";

  const signOutAdmin = () => {
    clearAdminSession();
    window.location.href = "/adminlogin";
  };

  return {
    role: role ?? (adminSession ? "admin" : null),
    isAdmin,
    isModerator,
    canMutate: isAdmin,
    signOutAdmin,
    rejectModeratorAction: () => {
      if (isModerator) {
        toast.error("Moderators cannot perform this action");
        return true;
      }
      return false;
    },
  };
}
