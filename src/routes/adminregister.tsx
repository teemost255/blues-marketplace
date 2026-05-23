import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { ShieldCheck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card } from "@/components/ui/card";
import { registerAdmin, storeAdminSession, getAdminSession } from "@/lib/admin-auth";

export const Route = createFileRoute("/adminregister")({
  component: AdminRegister,
  head: () => ({ meta: [{ title: "Admin Register — BluesMarketplace" }] }),
});

function AdminRegister() {
  const navigate = useNavigate();
  const [displayName, setDisplayName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirm, setConfirm] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    const session = getAdminSession();
    if (session) navigate({ to: "/admin" });
  }, [navigate]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (password !== confirm) {
      toast.error("Passwords do not match");
      return;
    }
    setSubmitting(true);
    try {
      const { session, error } = await registerAdmin(email, password, displayName || null);
      if (error || !session) {
        toast.error(error ?? "Registration failed");
        return;
      }
      storeAdminSession(session);
      toast.success(`Account created. Welcome, ${session.display_name ?? session.email}!`);
      navigate({ to: "/admin" });
    } catch (err) {
      console.error("Admin register error:", err);
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
          <h2 className="text-3xl font-bold leading-tight">Create an admin account.</h2>
          <p className="mt-3 text-white/80">
            Register your admin credentials to manage the platform.
          </p>
        </div>
        <div className="text-sm text-white/60">Restricted access · Staff only</div>
      </div>

      <div className="flex items-center justify-center p-6">
        <Card className="w-full max-w-md p-8">
          <div className="flex items-center gap-2 mb-1">
            <ShieldCheck className="h-6 w-6 text-primary" />
            <h1 className="text-2xl font-bold tracking-tight">Admin register</h1>
          </div>
          <p className="mt-1 text-sm text-muted-foreground">
            Create a new admin account to access the dashboard.
          </p>

          <form onSubmit={handleSubmit} className="mt-6 space-y-4">
            <div>
              <Label htmlFor="displayName">Display name</Label>
              <Input
                id="displayName"
                type="text"
                value={displayName}
                onChange={(e) => setDisplayName(e.target.value)}
                className="mt-1.5"
                placeholder="Optional"
                autoComplete="name"
              />
            </div>
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
                autoComplete="new-password"
              />
            </div>
            <div>
              <Label htmlFor="confirm">Confirm password</Label>
              <Input
                id="confirm"
                type="password"
                required
                value={confirm}
                onChange={(e) => setConfirm(e.target.value)}
                className="mt-1.5"
                autoComplete="new-password"
              />
            </div>
            <Button type="submit" className="w-full" disabled={submitting}>
              {submitting ? "Creating account..." : "Create account"}
            </Button>
          </form>

          <p className="mt-6 text-center text-sm text-muted-foreground">
            Already have an account?{" "}
            <Link to="/adminlogin" className="font-medium text-accent hover:underline">
              Sign in
            </Link>
          </p>
        </Card>
      </div>
    </div>
  );
}
