import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { ShieldCheck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card } from "@/components/ui/card";
import { authenticateAdmin, storeAdminSession, getAdminSession } from "@/lib/admin-auth";

export const Route = createFileRoute("/adminlogin")({
  component: AdminLogin,
  head: () => ({ meta: [{ title: "Admin Sign in — BluesMarketplace" }] }),
});

function AdminLogin() {
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    const session = getAdminSession();
    if (session) navigate({ to: "/admin" });
  }, [navigate]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      const { session, error } = await authenticateAdmin(email, password);
      if (error || !session) {
        toast.error(error ?? "Authentication failed");
        return;
      }
      storeAdminSession(session);
      toast.success(`Welcome, ${session.display_name ?? session.email}!`);
      navigate({ to: "/admin" });
    } catch (err) {
      console.error("Admin login error:", err);
      toast.error("An unexpected error occurred");
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="grid min-h-screen md:grid-cols-2">
      <div
        className="hidden flex-col justify-between p-12 text-white md:flex"
        style={{ background: "var(--gradient-hero)" }}
      >
        <Link to="/" className="flex items-center gap-2 font-semibold">
          <ShieldCheck className="h-5 w-5" /> BluesMarketplace Admin
        </Link>
        <div>
          <h2 className="text-3xl font-bold leading-tight">Admin portal.</h2>
          <p className="mt-3 text-white/80">
            Sign in with your admin credentials to manage the platform.
          </p>
        </div>
        <div className="text-sm text-white/60">Restricted access · Staff only</div>
      </div>

      <div className="flex items-center justify-center p-6">
        <Card className="w-full max-w-md p-8">
          <div className="flex items-center gap-2 mb-1">
            <ShieldCheck className="h-6 w-6 text-primary" />
            <h1 className="text-2xl font-bold tracking-tight">Admin sign in</h1>
          </div>
          <p className="mt-1 text-sm text-muted-foreground">
            Enter your admin credentials to access the dashboard.
          </p>

          <form onSubmit={handleSubmit} className="mt-6 space-y-4">
            <div>
              <Label htmlFor="email">Email</Label>
              <Input
                id="email"
                type="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="mt-1.5"
                autoComplete="username"
              />
            </div>
            <div>
              <Label htmlFor="password">Password</Label>
              <Input
                id="password"
                type="password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="mt-1.5"
                autoComplete="current-password"
              />
            </div>
            <Button type="submit" className="w-full" disabled={submitting}>
              {submitting ? "Signing in..." : "Sign in"}
            </Button>
          </form>

          <p className="mt-6 text-center text-sm text-muted-foreground">
            <Link to="/" className="font-medium text-accent hover:underline">
              ← Back to marketplace
            </Link>
          </p>
        </Card>
      </div>
    </div>
  );
}
