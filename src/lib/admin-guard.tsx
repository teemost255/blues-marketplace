import { useEffect, type ReactNode } from "react";
import { useNavigate } from "@tanstack/react-router";
import { toast } from "sonner";
import { useAuth } from "@/lib/auth";
import { getAdminSession, clearAdminSession } from "@/lib/admin-auth";

export function AdminGuard({ children }: { children: ReactNode }) {
  const { user, role, loading } = useAuth();
  const navigate = useNavigate();
  const adminSession = getAdminSession();

  // Check both traditional admin role (from user_roles) and new admin session
  const isAdmin = role === "admin" || !!adminSession;

  useEffect(() => {
    if (!loading && !isAdmin) {
      if (user && role === "user") {
        navigate({ to: "/dashboard" });
      } else if (!user && !adminSession) {
        // Not authenticated at all
        navigate({ to: "/admin/login" });
      }
    }
  }, [loading, user, role, isAdmin, adminSession, navigate]);

  if (loading) {
    return <div className="grid min-h-screen place-items-center text-muted-foreground">Loading…</div>;
  }

  if (!isAdmin) {
    return (
      <div className="grid min-h-screen place-items-center px-4 text-center">
        <div className="max-w-md rounded-3xl border border-red-200 bg-red-50 p-10 shadow-sm">
          <h1 className="text-2xl font-semibold text-red-900">Admin access required</h1>
          <p className="mt-3 text-sm text-red-700">
            Sign in with an admin account to view this section.
          </p>
          <button
            type="button"
            className="mt-6 inline-flex items-center justify-center rounded-full bg-red-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
            onClick={() => navigate({ to: "/admin/login" })}
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
  const adminSession = getAdminSession();

  // Check if already authenticated as admin
  const isAdminAuthenticated = role === "admin" || !!adminSession;

  useEffect(() => {
    if (!loading) {
      if (isAdminAuthenticated) {
        // Already admin, go to admin dashboard
        navigate({ to: "/admin" });
      } else if (user && role !== "admin") {
        // Regular user, send to dashboard
        navigate({ to: "/dashboard" });
      }
    }
  }, [loading, user, role, isAdminAuthenticated, navigate, adminSession]);

  if (loading) {
    return <div className="grid min-h-screen place-items-center text-muted-foreground">Loading…</div>;
  }

  return <>{children}</>;
}

export function useAdminPermissions() {
  const { role } = useAuth();
  const adminSession = getAdminSession();
  
  const isAdmin = role === "admin" || !!adminSession;
  const isModerator = role === "moderator";

  const signOutAdmin = () => {
    clearAdminSession();
    window.location.href = "/admin/login";
  };

  return {
    role,
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
