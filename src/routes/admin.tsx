import { createFileRoute, Link, Outlet, useRouterState } from "@tanstack/react-router";
import { LayoutDashboard, Package, Users, Receipt, Anchor, LogOut } from "lucide-react";
import { useAuth } from "@/lib/auth";
import { Button } from "@/components/ui/button";

export const Route = createFileRoute("/admin")({
  component: AdminLayout,
});

function AdminLayout() {
  const { user, role, loading, signOut } = useAuth();
  const pathname = useRouterState({ select: (s) => s.location.pathname });

  if (loading) return <div className="grid min-h-screen place-items-center text-muted-foreground">Loading…</div>;
  if (!user) return (
    <div className="grid min-h-screen place-items-center px-4 text-center">
      <div><h1 className="text-2xl font-semibold">Please sign in</h1><Button asChild className="mt-4"><Link to="/login">Sign in</Link></Button></div>
    </div>
  );
  if (role !== "admin") return (
    <div className="grid min-h-screen place-items-center px-4 text-center">
      <div>
        <h1 className="text-2xl font-semibold">Admin access required</h1>
        <p className="mt-2 text-sm text-muted-foreground">Your account doesn't have admin privileges.</p>
        <Button asChild className="mt-4"><Link to="/dashboard">Back to dashboard</Link></Button>
      </div>
    </div>
  );

  const links = [
    { to: "/admin", label: "Overview", icon: LayoutDashboard, exact: true },
    { to: "/admin/listings", label: "Listings", icon: Package },
    { to: "/admin/users", label: "Users", icon: Users },
    { to: "/admin/transactions", label: "Transactions", icon: Receipt },
  ];

  return (
    <div className="flex min-h-screen bg-muted/30">
      <aside className="hidden w-60 flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground md:flex">
        <div className="p-5">
          <Link to="/" className="flex items-center gap-2 font-semibold">
            <Anchor className="h-5 w-5 text-sidebar-primary" /> Admin Panel
          </Link>
        </div>
        <nav className="flex-1 space-y-1 px-3">
          {links.map((l) => {
            const active = l.exact ? pathname === l.to : pathname.startsWith(l.to);
            const Icon = l.icon;
            return (
              <Link key={l.to} to={l.to} className={`flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors ${active ? "bg-sidebar-accent text-sidebar-accent-foreground" : "hover:bg-sidebar-accent/60"}`}>
                <Icon className="h-4 w-4" />{l.label}
              </Link>
            );
          })}
        </nav>
        <div className="p-3">
          <Button variant="ghost" className="w-full justify-start text-sidebar-foreground hover:bg-sidebar-accent/60" onClick={signOut}>
            <LogOut className="mr-2 h-4 w-4" /> Sign out
          </Button>
        </div>
      </aside>
      <main className="flex-1"><Outlet /></main>
    </div>
  );
}
