import { createFileRoute, Link, Outlet, useRouterState } from "@tanstack/react-router";
import { useState } from "react";
import { Activity, FileText, LayoutDashboard, Menu, Settings, ShieldCheck, ShoppingBag, Ticket, Users } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Sheet, SheetContent, SheetTrigger, SheetTitle } from "@/components/ui/sheet";
import { useAdminPermissions } from "@/lib/admin-guard";
import { useAuth } from "@/lib/auth";
import { getAdminSession } from "@/lib/admin-auth";

export const Route = createFileRoute("/admin")({
  component: AdminLayout,
});

const navLinks = [
  { to: "/admin", label: "Overview", icon: LayoutDashboard, exact: true },
  { to: "/admin/users", label: "Users", icon: Users },
  { to: "/admin/listings", label: "Listings", icon: ShoppingBag },
  { to: "/admin/transactions", label: "Transactions", icon: Activity },
  { to: "/admin/tickets", label: "Tickets", icon: Ticket },
  { to: "/admin/audit", label: "Audit", icon: FileText },
  { to: "/admin/settings", label: "Settings", icon: Settings },
];

function AdminLayout() {
  const { user, signOut } = useAuth();
  const { signOutAdmin } = useAdminPermissions();
  const adminSession = getAdminSession();
  const pathname = useRouterState({ select: (s) => s.location.pathname });
  const [open, setOpen] = useState(false);

  // Determine if this is a traditional admin or new admin session
  const isNewAdmin = !!adminSession;
  const adminEmail = isNewAdmin ? adminSession?.email : user?.email;
  const adminDisplayName = isNewAdmin ? adminSession?.display_name : user?.email;

  const handleSignOut = async () => {
    if (isNewAdmin) {
      // New admin auth - clear session and redirect
      signOutAdmin();
    } else {
      // Traditional admin auth - use Supabase signOut
      await signOut();
    }
  };

  const SidebarContent = ({ onNav }: { onNav?: () => void }) => (
    <>
      <div className="p-5">
        <Link to="/admin" onClick={onNav} className="flex items-center gap-2 font-semibold text-red-900">
          <ShieldCheck className="h-5 w-5" /> Admin panel
        </Link>
        <div className="mt-3 text-xs text-red-700">Admin · {adminEmail}</div>
      </div>
      <nav className="flex-1 space-y-1 overflow-y-auto px-3">
        {navLinks.map((link) => {
          const active = link.exact ? pathname === link.to : pathname.startsWith(link.to);
          const Icon = link.icon;
          return (
            <Link
              key={link.to}
              to={link.to}
              onClick={onNav}
              className={`flex items-center gap-2 rounded-md px-3 py-2 text-sm transition ${
                active ? "bg-red-600 text-white" : "text-red-900 hover:bg-red-100"
              }`}
            >
              <Icon className="h-4 w-4" />
              {link.label}
            </Link>
          );
        })}
      </nav>
      <div className="space-y-2 p-3">
        <Button
          variant="outline"
          className="w-full text-red-900 hover:bg-red-100"
          onClick={handleSignOut}
        >
          Sign out
        </Button>
      </div>
    </>
  );

  return (
      <div className="flex min-h-screen bg-red-50">
        <aside className="hidden w-72 flex-col border-r border-red-200 bg-red-50 text-red-900 md:flex">
          <SidebarContent />
        </aside>

        <div className="flex min-w-0 flex-1 flex-col">
          <header className="sticky top-0 z-30 flex h-14 items-center justify-between gap-2 border-b border-red-200 bg-white/95 px-4 text-red-900 backdrop-blur md:hidden">
            <Sheet open={open} onOpenChange={setOpen}>
              <SheetTrigger asChild>
                <Button variant="ghost" size="icon" aria-label="Menu">
                  <Menu className="h-5 w-5" />
                </Button>
              </SheetTrigger>
              <SheetContent side="left" className="w-72 bg-red-50 p-0 text-red-900">
                <SheetTitle className="sr-only">Admin menu</SheetTitle>
                <div className="flex h-full flex-col">
                  <SidebarContent onNav={() => setOpen(false)} />
                </div>
              </SheetContent>
            </Sheet>
            <div className="flex items-center gap-2">
              <ShieldCheck className="h-4 w-4" />
              <span className="font-semibold">Admin</span>
            </div>
            <div className="text-sm text-red-700">Admin</div>
          </header>

          <main className="flex-1">
            <div className="border-b border-red-200 bg-red-50 px-4 py-4 md:px-8">
              <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                  <h1 className="text-2xl font-bold tracking-tight text-red-900">Admin dashboard</h1>
                  <div className="mt-1 flex flex-wrap items-center gap-2 text-sm text-red-700">
                    <span>Admin · {adminEmail}</span>
                    <span className="rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">Admin</span>
                  </div>
                </div>
              </div>
            </div>
            <Outlet />
          </main>
        </div>
      </div>
  );
}
