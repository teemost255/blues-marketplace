import { createFileRoute, Link, Outlet, useRouterState } from "@tanstack/react-router";
import { useState } from "react";
import { LayoutDashboard, Package, Users, Receipt, Anchor, LogOut, Settings, FileClock, Sparkles, HelpCircle, Menu } from "lucide-react";
import { useAuth } from "@/lib/auth";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Sheet, SheetContent, SheetTrigger, SheetTitle } from "@/components/ui/sheet";

export const Route = createFileRoute("/admin")({
  component: AdminLayout,
});

const secondary = [
  { to: "/dashboard", label: "User dashboard", icon: Sparkles },
  { to: "/marketplace", label: "Marketplace", icon: Sparkles },
];

function AdminLayout() {
  const { user, role, isStaff, loading, signOut } = useAuth();
  const pathname = useRouterState({ select: (s) => s.location.pathname });
  const [open, setOpen] = useState(false);
  const isAdmin = role === "admin";

  const links = [
    { to: "/admin", label: "Overview", icon: LayoutDashboard, exact: true, show: true },
    { to: "/admin/listings", label: "Listings", icon: Package, show: true },
    { to: "/admin/users", label: "Users", icon: Users, show: true },
    { to: "/admin/transactions", label: "Transactions", icon: Receipt, show: true },
    { to: "/admin/audit", label: "Audit log", icon: FileClock, show: isAdmin },
    { to: "/admin/settings", label: "Site settings", icon: Settings, show: isAdmin },
  ].filter((l) => l.show);

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

  const SidebarContent = ({ onNav }: { onNav?: () => void }) => (
    <>
      <div className="p-5">
        <Link to="/" onClick={onNav} className="flex items-center gap-2 font-semibold">
          <Anchor className="h-5 w-5 text-sidebar-primary" /> Admin Panel
        </Link>
        <Badge variant="secondary" className="mt-2 text-[10px] uppercase">{role}</Badge>
      </div>
      <nav className="flex-1 space-y-1 px-3 overflow-y-auto">
        <div className="px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-sidebar-foreground/50">Management</div>
        {links.map((l) => {
          const active = l.exact ? pathname === l.to : pathname.startsWith(l.to);
          const Icon = l.icon;
          return (
            <Link
              key={l.to}
              to={l.to}
              onClick={onNav}
              className={`flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors ${active ? "bg-sidebar-accent text-sidebar-accent-foreground" : "hover:bg-sidebar-accent/60"}`}>
              <Icon className="h-4 w-4" />{l.label}
            </Link>
          );
        })}
        <div className="mt-4 px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-sidebar-foreground/50">Explore</div>
        {secondary.map((l) => {
          const Icon = l.icon;
          return (
            <Link key={l.to} to={l.to} onClick={onNav} className="flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-sidebar-accent/60">
              <Icon className="h-4 w-4" />{l.label}
            </Link>
          );
        })}
      </nav>
      <div className="space-y-1 p-3">
        <button className="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-sidebar-foreground hover:bg-sidebar-accent/60">
          <HelpCircle className="h-4 w-4" /> Help & support
        </button>
        <Button variant="ghost" className="w-full justify-start text-sidebar-foreground hover:bg-sidebar-accent/60" onClick={signOut}>
          <LogOut className="mr-2 h-4 w-4" /> Sign out
        </Button>
      </div>
    </>
  );

  return (
    <div className="flex min-h-screen bg-muted/30">
      <aside className="hidden w-60 flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground md:flex">
        <SidebarContent />
      </aside>

      <div className="flex flex-1 flex-col">
        <header className="sticky top-0 z-30 flex h-14 items-center justify-between gap-2 border-b bg-background/80 px-4 backdrop-blur md:hidden">
          <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>
              <Button variant="ghost" size="icon" aria-label="Menu"><Menu className="h-5 w-5" /></Button>
            </SheetTrigger>
            <SheetContent side="left" className="w-72 bg-sidebar p-0 text-sidebar-foreground">
              <SheetTitle className="sr-only">Admin menu</SheetTitle>
              <div className="flex h-full flex-col"><SidebarContent onNav={() => setOpen(false)} /></div>
            </SheetContent>
          </Sheet>
          <Link to="/" className="flex items-center gap-2 font-semibold">
            <Anchor className="h-4 w-4 text-accent" /> Admin Panel
          </Link>
          <div className="flex items-center gap-1">
            <Button variant="ghost" size="icon" aria-label="Help"><HelpCircle className="h-4 w-4" /></Button>
          </div>
        </header>
        <main className="flex-1 p-6"><Outlet /></main>
      </div>
    </div>
  );
}
