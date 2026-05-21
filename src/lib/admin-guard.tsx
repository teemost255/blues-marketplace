import { useEffect, type ReactNode } from "react";
import { useNavigate } from "@tanstack/react-router";
import { toast } from "sonner";
import { useAuth } from "@/lib/auth";

export function AdminGuard({ children }: { children: ReactNode }) {
  const { user, role, loading } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (!loading && user && role === "user") {
      navigate({ to: "/dashboard" });
    }
  }, [loading, user, role, navigate]);

  if (loading) {
    return <div className="grid min-h-screen place-items-center text-muted-foreground">Loading…</div>;
  }

  if (!user) {
    return (
      <div className="grid min-h-screen place-items-center px-4 text-center">
        <div className="max-w-md rounded-3xl border border-red-200 bg-red-50 p-10 shadow-sm">
          <h1 className="text-2xl font-semibold text-red-900">Admin access required</h1>
          <p className="mt-3 text-sm text-red-700">
            Sign in with an admin or moderator account to view this section.
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

  if (role === "user") {
    return null;
  }

  return <>{children}</>;
}

export function AdminLoginGuard({ children }: { children: ReactNode }) {
  const { user, role, loading } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (!loading && user) {
      if (role === "admin") {
        navigate({ to: "/admin" });
      } else {
        navigate({ to: "/dashboard" });
      }
    }
  }, [loading, user, role, navigate]);

  if (loading) {
    return <div className="grid min-h-screen place-items-center text-muted-foreground">Loading…</div>;
  }

  return <>{children}</>;
}

export function useAdminPermissions() {
  const { role } = useAuth();
  const isAdmin = role === "admin";
  const isModerator = role === "moderator";

  return {
    role,
    isAdmin,
    isModerator,
    canMutate: isAdmin,
    rejectModeratorAction: () => {
      if (isModerator) {
        toast.error("Moderators cannot perform this action");
        return true;
      }
      return false;
    },
  };
}
