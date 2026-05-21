import { createFileRoute, Link, Outlet, useRouterState } from "@tanstack/react-router";
import { useState } from "react";
import { LayoutDashboard, Package, Users, Receipt, Anchor, LogOut, Settings, FileClock, Menu } from "lucide-react";
import { useAuth } from "@/lib/auth";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Sheet, SheetContent, SheetTrigger, SheetTitle } from "@/components/ui/sheet";

export const Route = createFileRoute("/admin")({
  component: AdminLayout,
});

const links = [
  { to: "/admin", label: "Overview", icon: LayoutDashboard, exact: true },
  { to: "/admin/listings", label: "Listings", icon: Package },
  { to: "/admin/users", label: "Users", icon: Users },
  { to: "/admin/transactions", label: "Transactions", icon: Receipt },
  { to: "/admin/audit", label: "Audit log", icon: FileClock },
  { to: "/admin/settings", label: "Site settings", icon: Settings },
];

function SidebarContent({ onNav, role }: { onNav?: () => void; role?: string }) {
  const pathname = useRouterState({ select: (s) => s.location.pathname });
  const isAdmin = role === "admin";
  const navLinks = links.filter((link) => {
    if (link.to === "/admin/audit" || link.to === "/admin/settings") {
      return isAdmin;
    }
    return true;
  });

  return (
    <>
      <div className="p-5">
        <Link to="/" onClick={onNav} className="flex items-center gap-2 font-semibold">
          <Anchor className="h-5 w-5 text-sidebar-primary" /> Admin Panel
        </Link>
        <div className="mt-2 text-xs text-sidebar-foreground/60">Manage listings, users and site settings.</div>
      </div>
      <nav className="flex-1 space-y-1 px-3 overflow-y-auto">
        <div className="px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-sidebar-foreground/50">Admin menu</div>
        {navLinks.map((l) => {
          const active = l.exact ? pathname === l.to : pathname.startsWith(l.to);
          const Icon = l.icon;
          return (
            <Link
              key={l.to}
              to={l.to}
              onClick={onNav}
              className={`flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors ${
                active ? "bg-sidebar-accent text-sidebar-accent-foreground" : "hover:bg-sidebar-accent/60"
              }`}
            >
              <Icon className="h-4 w-4" />{l.label}
            </Link>
          );
        })}
      </nav>
    </>
  );
}

function AdminLayout() {
  const { user, role, isStaff, loading, signOut } = useAuth();
  const pathname = useRouterState({ select: (s) => s.location.pathname });
  const [open, setOpen] = useState(false);

  // CRITICAL: If the app is still loading user data/roles, do not redirect.
  // Wait until loading is false.
  if (loading) {
    return (
      <div className="grid min-h-screen place-items-center text-muted-foreground">
        Checking access...
      </div>
    );
  }

  // If loading is finished and there is no user, then redirect/show login.
  if (!user) {
    return (
      <div className="grid min-h-screen place-items-center px-4 text-center">
        <div>
          <h1 className="text-2xl font-semibold">Please sign in</h1>
          <Button asChild className="mt-4"><Link to="/login">Sign in</Link></Button>
        </div>
      </div>
    );
  }

  // If loading is finished and user is not staff, show access denied.
  if (!isStaff) {
    return (
      <div className="grid min-h-screen place-items-center px-4 text-center">
        <div>
          <h1 className="text-2xl font-semibold">Admin access required</h1>
          <p className="mt-2 text-sm text-muted-foreground">
            Your account doesn't have sufficient privileges.
          </p>
          <Button asChild className="mt-4"><Link to="/dashboard">Back to dashboard</Link></Button>
        </div>
      </div>
    );
  }

  const isAdmin = role === "admin";

  return (
    <div className="flex min-h-screen bg-muted/30">
      <aside className="hidden w-60 flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground md:flex">
        <SidebarContent role={role} />
        <div className="p-3">
          <Button variant="ghost" className="w-full justify-start text-sidebar-foreground hover:bg-sidebar-accent/60" onClick={signOut}>
            <LogOut className="mr-2 h-4 w-4" /> Sign out
          </Button>
        </div>
      </aside>

      <div className="flex flex-1 flex-col">
        <header className="sticky top-0 z-30 flex h-14 items-center justify-between gap-2 border-b bg-background/80 px-4 backdrop-blur md:hidden">
          <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>
              <Button variant="ghost" size="icon" aria-label="Menu"><Menu className="h-5 w-5" /></Button>
            </SheetTrigger>
            <SheetContent side="left" className="w-72 bg-sidebar p-0 text-sidebar-foreground">
              <SheetTitle className="sr-only">Admin menu</SheetTitle>
              <div className="flex h-full flex-col"><SidebarContent onNav={() => setOpen(false)} role={role} /></div>
            </SheetContent>
          </Sheet>
          <div className="flex items-center gap-2 font-semibold">
            <Anchor className="h-4 w-4 text-sidebar-primary" /> Admin Panel
          </div>
          <div className="flex items-center gap-1">
            <Button variant="ghost" size="icon" aria-label="Sign out" onClick={signOut}><LogOut className="h-4 w-4" /></Button>
          </div>
        </header>

        <main className="flex-1 p-6"><Outlet /></main>
      </div>
    </div>
  );
}
