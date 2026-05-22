import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { Anchor } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card } from "@/components/ui/card";
import { supabase } from "@/integrations/supabase/client";
import { lovable } from "@/integrations/lovable";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/login")({
  component: Login,
  head: () => ({ meta: [{ title: "Sign in — BluesMarketplace" }] }),
});

export interface LoginFormProps {
  redirectTo?: string;
  title?: string;
  subtitle?: string;
}

export function LoginForm({
  redirectTo = "/dashboard",
  title = "Sign in",
  subtitle = "Welcome back. Please enter your details.",
}: LoginFormProps) {
  const navigate = useNavigate();
  const { user, role } = useAuth();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [submitting, setSubmitting] = useState(false);

  const resolveRedirect = () => {
    if (role === "admin" || role === "moderator") return "/admin";
    if (redirectTo !== "/dashboard") return redirectTo;
    return "/dashboard";
  };

  useEffect(() => {
    if (!user || role === null) return;
    navigate({ to: resolveRedirect() });
  }, [user, role, navigate, redirectTo]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      const { error } = await supabase.auth.signInWithPassword({
        email: email.toLowerCase().trim(),
        password,
      });
      if (error) {
        toast.error(error.message);
        return;
      }
      toast.success("Welcome back!");
    } catch (err) {
      console.error("Login error:", err);
      toast.error("An unexpected error occurred");
    } finally {
      setSubmitting(false);
    }
  };

  const handleGoogle = async () => {
    const result = await lovable.auth.signInWithOAuth("google", {
      // Use origin as the OAuth redirect URI so the SDK can handle the callback
      redirect_uri: window.location.origin,
    });

    if (result.error) {
      toast.error("Google sign-in failed");
      return;
    }

    // If the provider didn't perform a full-page redirect (tokens returned inline),
    // navigate to the desired destination so the UI updates immediately.
    if (!result.redirected) {
      toast.success("Signed in with Google");
      if (role !== null) {
        navigate({ to: resolveRedirect() });
      }
    }
  };

  return (
    <div className="grid min-h-screen md:grid-cols-2">
      <div
        className="hidden flex-col justify-between p-12 text-white md:flex"
        style={{ background: "var(--gradient-hero)" }}
      >
        <Link to="/" className="flex items-center gap-2 font-semibold">
          <Anchor className="h-5 w-5" /> BluesMarketplace
        </Link>
        <div>
          <h2 className="text-3xl font-bold leading-tight">
            Welcome back to the marketplace.
          </h2>
          <p className="mt-3 text-white/80">
            Sign in to browse, purchase, and manage your digital products.
          </p>
        </div>
        <div className="text-sm text-white/60">
          Secure auth · Paystack checkout
        </div>
      </div>

      <div className="flex items-center justify-center p-6">
        <Card className="w-full max-w-md p-8">
          <h1 className="text-2xl font-bold tracking-tight">{title}</h1>
          <p className="mt-1 text-sm text-muted-foreground">{subtitle}</p>

          <Button
            variant="outline"
            className="mt-6 w-full"
            onClick={handleGoogle}
          >
            <svg className="mr-2 h-4 w-4" viewBox="0 0 24 24">
              <path
                fill="#4285F4"
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
              />
              <path
                fill="#34A853"
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
              />
              <path
                fill="#FBBC05"
                d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.84z"
              />
              <path
                fill="#EA4335"
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"
              />
            </svg>
            Continue with Google
          </Button>

          <div className="my-6 flex items-center gap-3 text-xs text-muted-foreground">
            <div className="h-px flex-1 bg-border" />
            OR
            <div className="h-px flex-1 bg-border" />
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <Label htmlFor="email">Email</Label>
              <Input
                id="email"
                type="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="mt-1.5"
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
              />
            </div>
            <Button type="submit" className="w-full" disabled={submitting}>
              {submitting ? "Signing in..." : "Sign in"}
            </Button>
          </form>

          <p className="mt-6 text-center text-sm text-muted-foreground">
            Don't have an account?{" "}
            <Link
              to="/register"
              className="font-medium text-accent hover:underline"
            >
              Register
            </Link>
          </p>
        </Card>
      </div>
    </div>
  );
}

function Login() {
  return <LoginForm />;
}
