import { createFileRoute, Link, Outlet, useRouterState } from "@tanstack/react-router";
import { useState } from "react";
import { Anchor, Bell, Heart, HelpCircle, LayoutDashboard, LifeBuoy, LogOut, Menu, Settings, ShoppingBag, Sparkles, Ticket, User as UserIcon, Wallet } from "lucide-react";
import { useAuth } from "@/lib/auth";
import { Button } from "@/components/ui/button";
import { Sheet, SheetContent, SheetTrigger, SheetTitle } from "@/components/ui/sheet";
import { NotificationBell } from "@/components/NotificationBell";

export const Route = createFileRoute("/dashboard")({
  component: DashboardLayout,
});

const accountLinks = [
  { to: "/dashboard", label: "Overview", icon: LayoutDashboard, exact: true },
  { to: "/dashboard/wallet", label: "Wallet", icon: Wallet },
  { to: "/dashboard/orders", label: "My orders", icon: ShoppingBag },
  { to: "/dashboard/wishlist", label: "Wishlist", icon: Heart },
  { to: "/dashboard/notifications", label: "Notifications", icon: Bell },
  { to: "/dashboard/support", label: "Support", icon: LifeBuoy },
  { to: "/dashboard/profile", label: "Profile", icon: UserIcon },
];

const exploreLinks = [{ to: "/marketplace", label: "Marketplace", icon: Sparkles }];

function DashboardLayout() {
  const { user, loading, signOut, role } = useAuth();
  const pathname = useRouterState({ select: (s) => s.location.pathname });
  const [open, setOpen] = useState(false);

  if (loading) return <div className="grid min-h-screen place-items-center text-muted-foreground">Loading…</div>;
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

  const SidebarContent = ({ onNav }: { onNav?: () => void }) => (
    <>
      <div className="p-5">
        <Link to="/" onClick={onNav} className="flex items-center gap-2 font-semibold">
          <Anchor className="h-5 w-5 text-sidebar-primary" /> BluesMarketplace
        </Link>
        <div className="mt-1 truncate text-xs text-sidebar-foreground/60">{role === "admin" ? "Admin · " : ""}{user.email}</div>
      </div>
      <nav className="flex-1 space-y-1 overflow-y-auto px-3">
        <div className="px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-sidebar-foreground/50">Account</div>
        {accountLinks.map((l) => {
          const active = l.exact ? pathname === l.to : pathname.startsWith(l.to);
          const Icon = l.icon;
          return (
            <Link key={l.to} to={l.to} onClick={onNav} className={`flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors ${active ? "bg-sidebar-accent text-sidebar-accent-foreground" : "hover:bg-sidebar-accent/60"}`}>
              <Icon className="h-4 w-4" />{l.label}
            </Link>
          );
        })}
        <div className="mt-4 px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-sidebar-foreground/50">Explore</div>
        {exploreLinks.map((l) => {
          const Icon = l.icon;
          return (
            <Link key={l.to} to={l.to} onClick={onNav} className="flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-sidebar-accent/60">
              <Icon className="h-4 w-4" />{l.label}
            </Link>
          );
        })}
{user && (
          <>
            <div className="mt-4 px-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-sidebar-foreground/50">Staff</div>
            <Link to="/admin" onClick={onNav} className="flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-sidebar-accent/60"><Settings className="h-4 w-4" /> Admin panel</Link>
            <Link to="/admin/tickets" onClick={onNav} className="flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-sidebar-accent/60"><Ticket className="h-4 w-4" /> Tickets</Link>
          </>
        )}
      </nav>
      <div className="space-y-1 p-3">
        <Link to="/dashboard/support" onClick={onNav} className="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-sidebar-foreground hover:bg-sidebar-accent/60">
          <HelpCircle className="h-4 w-4" /> Help & support
        </Link>
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

      <div className="flex min-w-0 flex-1 flex-col">
        <header className="sticky top-0 z-30 flex h-14 items-center justify-between gap-2 border-b bg-background/80 px-4 backdrop-blur md:hidden">
          <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>
              <Button variant="ghost" size="icon" aria-label="Menu"><Menu className="h-5 w-5" /></Button>
            </SheetTrigger>
            <SheetContent side="left" className="w-72 bg-sidebar p-0 text-sidebar-foreground">
              <SheetTitle className="sr-only">Dashboard menu</SheetTitle>
              <div className="flex h-full flex-col"><SidebarContent onNav={() => setOpen(false)} /></div>
            </SheetContent>
          </Sheet>
          <Link to="/" className="flex items-center gap-2 font-semibold">
            <Anchor className="h-4 w-4 text-accent" /> BluesMarketplace
          </Link>
          <div className="flex items-center gap-1">
            <NotificationBell />
            <Button variant="ghost" size="icon" asChild aria-label="Wallet"><Link to="/dashboard/wallet"><Wallet className="h-4 w-4" /></Link></Button>
          </div>
        </header>
        <main className="flex-1"><Outlet /></main>
      </div>
    </div>
  );
}
