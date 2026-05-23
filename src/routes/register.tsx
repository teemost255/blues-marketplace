import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { Anchor, Eye, EyeOff } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Checkbox } from "@/components/ui/checkbox";
import { Progress } from "@/components/ui/progress";
import { api } from "@/lib/api";
import { useAuth } from "@/lib/auth";
import { AnimatedBlobs } from "@/components/AnimatedBlobs";

export const Route = createFileRoute("/register")({
  component: Register,
  head: () => ({ meta: [{ title: "Create account — BluesMarketplace" }] }),
});

const COUNTRIES = ["Nigeria", "Ghana", "Kenya", "South Africa", "United States", "United Kingdom", "Canada", "India", "Other"];

function passwordStrength(p: string) {
  let s = 0;
  if (p.length >= 8) s += 25;
  if (/[A-Z]/.test(p)) s += 25;
  if (/[0-9]/.test(p)) s += 25;
  if (/[^A-Za-z0-9]/.test(p)) s += 25;
  return s;
}

function Register() {
  const navigate = useNavigate();
  const { user } = useAuth();
  const [form, setForm] = useState({
    name: "",
    username: "",
    email: "",
    phone: "",
    country: "Nigeria",
    referral: "",
    password: "",
    confirm: "",
    agree: false,
  });
  const [showPw, setShowPw] = useState(false);
  const [loading, setLoading] = useState(false);

  useEffect(() => { if (user) navigate({ to: "/dashboard" }); }, [user, navigate]);

  const set = <K extends keyof typeof form>(k: K, v: typeof form[K]) => setForm((f) => ({ ...f, [k]: v }));
  const strength = passwordStrength(form.password);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.agree) return toast.error("You must accept the terms");
    if (form.password.length < 8) return toast.error("Password must be at least 8 characters");
    if (form.password !== form.confirm) return toast.error("Passwords do not match");
    if (!/^[a-zA-Z0-9_]{3,20}$/.test(form.username)) return toast.error("Username: 3-20 letters, numbers, underscores");
    setLoading(true);
    try {
      window.location.href = "/__replauthuser";
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="grid min-h-screen md:grid-cols-2">
      <div className="relative hidden flex-col justify-between overflow-hidden p-12 text-white md:flex" style={{ background: "var(--gradient-hero)" }}>
        <AnimatedBlobs withOrbits />
        <div className="relative z-10">
          <Link to="/" className="flex items-center gap-2 font-semibold">
            <Anchor className="h-5 w-5" /> BluesMarketplace
          </Link>
        </div>
        <div className="relative z-10">
          <h2 className="text-3xl font-bold leading-tight">Join thousands of buyers and sellers.</h2>
          <p className="mt-3 text-white/80">Create your free account and trade Facebook, Instagram, TikTok and 2nd-number assets safely.</p>
        </div>
        <div className="relative z-10 text-sm text-white/60">Free forever · No credit card required</div>
      </div>

      <div className="relative flex items-center justify-center overflow-hidden p-4 sm:p-6">
        <AnimatedBlobs />
        <Card className="relative z-10 w-full max-w-md p-6 sm:p-8">
          <h1 className="text-2xl font-bold tracking-tight">Create account</h1>
          <p className="mt-1 text-sm text-muted-foreground">Sign up to start buying and selling.</p>

          <Button variant="outline" className="mt-6 w-full" onClick={handleGoogle}>
            <svg className="mr-2 h-4 w-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/></svg>
            Continue with Google
          </Button>

          <div className="my-5 flex items-center gap-3 text-xs text-muted-foreground">
            <div className="h-px flex-1 bg-border" />OR<div className="h-px flex-1 bg-border" />
          </div>

          <form onSubmit={handleSubmit} className="space-y-3">
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div>
                <Label htmlFor="name">Full name</Label>
                <Input id="name" required value={form.name} onChange={(e) => set("name", e.target.value)} className="mt-1.5" />
              </div>
              <div>
                <Label htmlFor="username">Username</Label>
                <Input id="username" required value={form.username} onChange={(e) => set("username", e.target.value)} className="mt-1.5" placeholder="bluestrader" />
              </div>
            </div>
            <div>
              <Label htmlFor="email">Email</Label>
              <Input id="email" type="email" required value={form.email} onChange={(e) => set("email", e.target.value)} className="mt-1.5" />
            </div>
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div>
                <Label htmlFor="phone">Phone</Label>
                <Input id="phone" type="tel" required value={form.phone} onChange={(e) => set("phone", e.target.value)} className="mt-1.5" placeholder="+234..." />
              </div>
              <div>
                <Label>Country</Label>
                <Select value={form.country} onValueChange={(v) => set("country", v)}>
                  <SelectTrigger className="mt-1.5"><SelectValue /></SelectTrigger>
                  <SelectContent>{COUNTRIES.map((c) => <SelectItem key={c} value={c}>{c}</SelectItem>)}</SelectContent>
                </Select>
              </div>
            </div>
            <div>
              <Label htmlFor="referral">Referral code (optional)</Label>
              <Input id="referral" value={form.referral} onChange={(e) => set("referral", e.target.value)} className="mt-1.5" />
            </div>
            <div>
              <Label htmlFor="password">Password</Label>
              <div className="relative mt-1.5">
                <Input id="password" type={showPw ? "text" : "password"} required value={form.password} onChange={(e) => set("password", e.target.value)} />
                <button type="button" onClick={() => setShowPw((s) => !s)} className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground">
                  {showPw ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                </button>
              </div>
              {form.password && (
                <div className="mt-2">
                  <Progress value={strength} className="h-1" />
                  <p className="mt-1 text-xs text-muted-foreground">
                    {strength < 50 ? "Weak" : strength < 75 ? "Okay" : strength < 100 ? "Strong" : "Excellent"}
                  </p>
                </div>
              )}
            </div>
            <div>
              <Label htmlFor="confirm">Confirm password</Label>
              <Input id="confirm" type={showPw ? "text" : "password"} required value={form.confirm} onChange={(e) => set("confirm", e.target.value)} className="mt-1.5" />
            </div>
            <label className="flex items-start gap-2 text-sm text-muted-foreground">
              <Checkbox checked={form.agree} onCheckedChange={(v) => set("agree", v === true)} className="mt-0.5" />
              <span>
                I agree to the <Link to="/terms" className="text-accent hover:underline">Terms</Link> and <Link to="/privacy" className="text-accent hover:underline">Privacy Policy</Link>.
              </span>
            </label>
            <Button type="submit" className="w-full" disabled={loading}>
              {loading ? "Creating..." : "Create account"}
            </Button>
          </form>

          <p className="mt-5 text-center text-sm text-muted-foreground">
            Already have an account? <Link to="/login" className="font-medium text-accent hover:underline">Sign in</Link>
          </p>
        </Card>
      </div>
    </div>
  );
}
