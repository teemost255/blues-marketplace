import { createFileRoute, Link } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { useState } from "react";
import {
  ArrowRight, ShieldCheck, Zap, Wallet, Sparkles, Star, Clock, TrendingUp,
  Users, Package, CheckCircle2, Lock, Headphones, BadgeCheck, Mail,
  Facebook, Instagram, Music2, Phone,
} from "lucide-react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
  Accordion, AccordionContent, AccordionItem, AccordionTrigger,
} from "@/components/ui/accordion";
import { SiteHeader } from "@/components/SiteHeader";
import { SiteFooter } from "@/components/SiteFooter";
import { AnimatedBlobs } from "@/components/AnimatedBlobs";
import { supabase } from "@/integrations/supabase/client";
import { LISTING_CATEGORIES } from "@/lib/categories";

export const Route = createFileRoute("/")({
  component: Landing,
  head: () => ({
    meta: [
      { title: "BluesMarketplace — Trusted marketplace for digital accounts & 2nd numbers" },
      { name: "description", content: "Buy verified Facebook, Instagram, TikTok accounts and 2nd numbers with secure Paystack checkout, instant delivery, and wallet protection." },
      { property: "og:title", content: "BluesMarketplace — Digital marketplace" },
      { property: "og:description", content: "Verified digital accounts and 2nd numbers. Secure checkout, instant delivery, wallet-backed." },
    ],
  }),
});

const CATEGORY_META: Record<string, { icon: any; tint: string; blurb: string }> = {
  Facebook: { icon: Facebook, tint: "from-blue-500/20 to-blue-700/10", blurb: "Aged & verified profiles" },
  Instagram: { icon: Instagram, tint: "from-pink-500/20 to-purple-600/10", blurb: "Niche & creator accounts" },
  TikTok: { icon: Music2, tint: "from-rose-500/20 to-slate-700/10", blurb: "Monetizable handles" },
  "2nd Numbers": { icon: Phone, tint: "from-emerald-500/20 to-teal-600/10", blurb: "Global virtual numbers" },
};

function Landing() {
  const { data: featured } = useQuery({
    queryKey: ["featured-listings"],
    queryFn: async () => {
      const { data, error } = await supabase
        .from("listings").select("id,title,price,category,image_url")
        .eq("is_active", true).order("created_at", { ascending: false }).limit(6);
      if (error) throw error;
      return data ?? [];
    },
  });

  const { data: recent } = useQuery({
    queryKey: ["recent-listings"],
    queryFn: async () => {
      const { data } = await supabase
        .from("listings").select("id,title,price,category,created_at")
        .eq("is_active", true).order("created_at", { ascending: false }).limit(5);
      return data ?? [];
    },
  });

  const { data: stats } = useQuery({
    queryKey: ["home-stats"],
    queryFn: async () => {
      const [{ count: listings }, { count: users }, { count: orders }] = await Promise.all([
        supabase.from("listings").select("*", { count: "exact", head: true }).eq("is_active", true),
        supabase.from("profiles").select("*", { count: "exact", head: true }),
        supabase.from("purchases").select("*", { count: "exact", head: true }),
      ]);
      return { listings: listings ?? 0, users: users ?? 0, orders: orders ?? 0 };
    },
  });

  const [email, setEmail] = useState("");
  const subscribe = (e: React.FormEvent) => {
    e.preventDefault();
    if (!email.includes("@")) return toast.error("Enter a valid email");
    toast.success("Subscribed! Watch your inbox for new drops.");
    setEmail("");
  };

  return (
    <div className="flex min-h-screen flex-col">
      <SiteHeader />

      {/* HERO */}
      <section className="relative overflow-hidden">
        <div className="absolute inset-0 -z-10 opacity-90" style={{ background: "var(--gradient-hero)" }} />
        <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_right,oklch(0.74_0.08_190/0.3),transparent_60%)]" />
        <AnimatedBlobs withOrbits />
        <div className="container relative mx-auto px-4 py-16 md:py-28">
          <div className="max-w-3xl">
            <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur">
              <Sparkles className="h-3 w-3" />
              Facebook · Instagram · TikTok · 2nd Numbers
            </div>
            <h1 className="text-4xl font-bold tracking-tight text-white md:text-6xl">
              The trusted marketplace for{" "}
              <span className="bg-gradient-to-r from-[oklch(0.74_0.08_190)] to-white bg-clip-text text-transparent">
                digital accounts
              </span>
            </h1>
            <p className="mt-6 max-w-xl text-base text-white/80 md:text-lg">
              Buy verified accounts and second numbers from vetted sellers. Wallet-backed,
              instantly delivered, and protected by Paystack checkout.
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Button size="lg" asChild className="bg-white text-primary hover:bg-white/90">
                <Link to="/marketplace">Browse marketplace<ArrowRight className="ml-2 h-4 w-4" /></Link>
              </Button>
              <Button size="lg" variant="outline" asChild className="border-white/30 bg-white/10 text-white hover:bg-white/20 hover:text-white">
                <Link to="/register">Create free account</Link>
              </Button>
            </div>

            {/* trust pills */}
            <div className="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm text-white/80">
              <span className="flex items-center gap-2"><CheckCircle2 className="h-4 w-4 text-[oklch(0.74_0.08_190)]" /> Instant delivery</span>
              <span className="flex items-center gap-2"><Lock className="h-4 w-4 text-[oklch(0.74_0.08_190)]" /> Escrow wallet</span>
              <span className="flex items-center gap-2"><Headphones className="h-4 w-4 text-[oklch(0.74_0.08_190)]" /> 24/7 support</span>
            </div>
          </div>
        </div>
      </section>

      {/* STATS BAR */}
      <section className="border-b bg-card">
        <div className="container mx-auto grid grid-cols-2 gap-6 px-4 py-8 md:grid-cols-4">
          {[
            { label: "Active listings", value: stats?.listings ?? 0, icon: Package },
            { label: "Registered users", value: stats?.users ?? 0, icon: Users },
            { label: "Orders processed", value: stats?.orders ?? 0, icon: TrendingUp },
            { label: "Avg rating", value: "4.9★", icon: Star },
          ].map(({ label, value, icon: Icon }) => (
            <div key={label} className="flex items-center gap-3">
              <div className="grid h-11 w-11 place-items-center rounded-lg bg-accent/10 text-accent">
                <Icon className="h-5 w-5" />
              </div>
              <div>
                <div className="text-2xl font-bold tracking-tight">{value}</div>
                <div className="text-xs text-muted-foreground">{label}</div>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* POPULAR CATEGORIES */}
      <section className="container mx-auto px-4 py-16">
        <SectionHead eyebrow="Browse" title="Popular categories" subtitle="Jump straight into the niches buyers love." />
        <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {LISTING_CATEGORIES.map((cat) => {
            const meta = CATEGORY_META[cat];
            const Icon = meta.icon;
            return (
              <Link key={cat} to="/marketplace" search={{ category: cat } as never}>
                <Card className={`group relative overflow-hidden border-border/60 p-6 transition-all hover:-translate-y-1 hover:shadow-lg bg-gradient-to-br ${meta.tint}`}>
                  <Icon className="h-8 w-8 text-foreground/80" />
                  <div className="mt-4 font-semibold">{cat}</div>
                  <div className="text-xs text-muted-foreground">{meta.blurb}</div>
                  <ArrowRight className="absolute right-4 top-4 h-4 w-4 opacity-0 transition-opacity group-hover:opacity-100" />
                </Card>
              </Link>
            );
          })}
        </div>
      </section>

      {/* FEATURED LISTINGS */}
      <section className="container mx-auto px-4 pb-16">
        <div className="flex items-end justify-between">
          <SectionHead eyebrow="Hot" title="Featured listings" subtitle="Fresh drops from verified sellers." />
          <Button variant="ghost" asChild className="hidden sm:inline-flex"><Link to="/marketplace">View all<ArrowRight className="ml-1 h-4 w-4" /></Link></Button>
        </div>
        <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {(featured ?? []).map((l) => (
            <Link key={l.id} to="/marketplace/$id" params={{ id: l.id }}>
              <Card className="group overflow-hidden border-border/60 transition-all hover:-translate-y-1 hover:shadow-lg">
                <div className="aspect-[16/10] overflow-hidden bg-muted">
                  {l.image_url ? (
                    <img src={l.image_url} alt={l.title} loading="lazy" className="h-full w-full object-cover transition-transform group-hover:scale-105" />
                  ) : (
                    <div className="grid h-full w-full place-items-center text-muted-foreground" style={{ background: "var(--gradient-card)" }}>
                      <Sparkles className="h-8 w-8" />
                    </div>
                  )}
                </div>
                <div className="p-4">
                  <Badge variant="secondary" className="text-xs">{l.category}</Badge>
                  <div className="mt-2 line-clamp-1 font-semibold">{l.title}</div>
                  <div className="mt-2 text-lg font-bold">₦{Number(l.price).toLocaleString()}</div>
                </div>
              </Card>
            </Link>
          ))}
          {(!featured || featured.length === 0) && (
            <div className="col-span-full rounded-lg border border-dashed border-border p-12 text-center text-muted-foreground">
              No listings yet. Sign in as admin to create the first one.
            </div>
          )}
        </div>
      </section>

      {/* TRENDING + RECENT (split) */}
      <section className="container mx-auto grid gap-6 px-4 pb-16 lg:grid-cols-2">
        <Card className="p-6">
          <SectionHead small eyebrow="Trending" title="What buyers love this week" icon={TrendingUp} />
          <ul className="mt-5 space-y-3">
            {(featured ?? []).slice(0, 4).map((l, i) => (
              <li key={l.id}>
                <Link to="/marketplace/$id" params={{ id: l.id }} className="flex items-center gap-3 rounded-lg p-2 hover:bg-secondary">
                  <span className="grid h-8 w-8 place-items-center rounded-md bg-accent/10 text-sm font-bold text-accent">{i + 1}</span>
                  <div className="min-w-0 flex-1">
                    <div className="truncate text-sm font-medium">{l.title}</div>
                    <div className="text-xs text-muted-foreground">{l.category}</div>
                  </div>
                  <div className="text-sm font-bold">₦{Number(l.price).toLocaleString()}</div>
                </Link>
              </li>
            ))}
            {(!featured || featured.length === 0) && <li className="text-sm text-muted-foreground">No trending items yet.</li>}
          </ul>
        </Card>
        <Card className="p-6">
          <SectionHead small eyebrow="Just in" title="Recently added" icon={Clock} />
          <ul className="mt-5 space-y-3">
            {(recent ?? []).map((l) => (
              <li key={l.id}>
                <Link to="/marketplace/$id" params={{ id: l.id }} className="flex items-center gap-3 rounded-lg p-2 hover:bg-secondary">
                  <div className="min-w-0 flex-1">
                    <div className="truncate text-sm font-medium">{l.title}</div>
                    <div className="text-xs text-muted-foreground">{l.category} · {new Date(l.created_at).toLocaleDateString()}</div>
                  </div>
                  <div className="text-sm font-bold">₦{Number(l.price).toLocaleString()}</div>
                </Link>
              </li>
            ))}
            {(!recent || recent.length === 0) && <li className="text-sm text-muted-foreground">Nothing new yet.</li>}
          </ul>
        </Card>
      </section>

      {/* WHY CHOOSE US */}
      <section className="bg-muted/30 py-16">
        <div className="container mx-auto px-4">
          <SectionHead eyebrow="Why Blues" title="Why choose BluesMarketplace" centered
            subtitle="Built for buyers and sellers who want speed, safety, and zero surprises." />
          <div className="mt-10 grid gap-6 md:grid-cols-3">
            {[
              { icon: ShieldCheck, title: "Verified inventory", desc: "Every listing is reviewed before it goes live." },
              { icon: Zap, title: "Instant delivery", desc: "Credentials delivered the second payment clears." },
              { icon: Wallet, title: "Wallet protection", desc: "Funds held in escrow until you confirm receipt." },
              { icon: BadgeCheck, title: "Verified sellers", desc: "Sub-admin vetted accounts with reputation scoring." },
              { icon: Lock, title: "Encrypted checkout", desc: "Paystack-secured cards, transfers, USSD." },
              { icon: Headphones, title: "Real human support", desc: "Ticket replies in under an hour, 24/7." },
            ].map(({ icon: Icon, title, desc }) => (
              <Card key={title} className="border-border/60 p-6" style={{ boxShadow: "var(--shadow-card)" }}>
                <div className="mb-4 grid h-10 w-10 place-items-center rounded-lg bg-accent/10 text-accent"><Icon className="h-5 w-5" /></div>
                <h3 className="font-semibold">{title}</h3>
                <p className="mt-2 text-sm text-muted-foreground">{desc}</p>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* TESTIMONIALS */}
      <section className="container mx-auto px-4 py-16">
        <SectionHead eyebrow="Loved by buyers" title="What customers say" centered />
        <div className="mt-10 grid gap-6 md:grid-cols-3">
          {[
            { name: "Tobi A.", role: "Growth marketer", quote: "Got an aged IG handle in under 3 minutes. Smoothest purchase I've ever made online.", avatar: "T" },
            { name: "Aisha B.", role: "Agency owner", quote: "We source bulk 2nd numbers here every week. The wallet flow saves us hours of card pain.", avatar: "A" },
            { name: "Chinedu K.", role: "Content creator", quote: "Support resolved my issue in 12 minutes. That's better than most banks I deal with.", avatar: "C" },
          ].map((t) => (
            <Card key={t.name} className="p-6">
              <div className="flex gap-1 text-accent">{Array.from({ length: 5 }).map((_, i) => <Star key={i} className="h-4 w-4 fill-current" />)}</div>
              <p className="mt-4 text-sm text-foreground/90">"{t.quote}"</p>
              <div className="mt-6 flex items-center gap-3">
                <div className="grid h-10 w-10 place-items-center rounded-full bg-accent/10 font-semibold text-accent">{t.avatar}</div>
                <div>
                  <div className="text-sm font-medium">{t.name}</div>
                  <div className="text-xs text-muted-foreground">{t.role}</div>
                </div>
              </div>
            </Card>
          ))}
        </div>
      </section>

      {/* TRUST & SECURITY */}
      <section className="bg-primary text-primary-foreground">
        <div className="container mx-auto grid items-center gap-10 px-4 py-16 md:grid-cols-2">
          <div>
            <Badge variant="secondary" className="mb-4">Security first</Badge>
            <h2 className="text-3xl font-bold tracking-tight">Your money & data are protected, end to end.</h2>
            <p className="mt-4 text-primary-foreground/80">
              We combine Paystack-grade payment encryption, escrow wallets, fraud monitoring,
              and role-based admin controls so every transaction is auditable and reversible
              within our dispute window.
            </p>
            <ul className="mt-6 space-y-3 text-sm">
              {["256-bit SSL & PCI-DSS Paystack rails", "Escrow-held wallet balances", "24/7 fraud monitoring", "Buyer protection on every order"].map((t) => (
                <li key={t} className="flex items-center gap-2"><CheckCircle2 className="h-4 w-4 text-[oklch(0.74_0.08_190)]" />{t}</li>
              ))}
            </ul>
          </div>
          <div className="grid grid-cols-2 gap-4">
            {[
              { v: "0%", l: "Successful chargebacks" },
              { v: "<60s", l: "Avg delivery time" },
              { v: "99.98%", l: "Uptime last 90 days" },
              { v: "24/7", l: "Support coverage" },
            ].map((s) => (
              <div key={s.l} className="rounded-xl border border-white/15 bg-white/5 p-5 backdrop-blur">
                <div className="text-3xl font-bold">{s.v}</div>
                <div className="mt-1 text-xs text-primary-foreground/70">{s.l}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* PROMO BANNER */}
      <section className="container mx-auto px-4 py-16">
        <Card className="relative overflow-hidden border-0 p-0">
          <div className="absolute inset-0" style={{ background: "var(--gradient-hero)" }} />
          <AnimatedBlobs />
          <div className="relative flex flex-col items-start gap-6 p-8 text-white md:flex-row md:items-center md:justify-between md:p-12">
            <div>
              <Badge className="mb-3 bg-white/15 text-white hover:bg-white/20">Limited time</Badge>
              <h3 className="text-2xl font-bold md:text-3xl">Get ₦1,000 free wallet credit on signup</h3>
              <p className="mt-2 max-w-xl text-white/80">Top up your wallet within 24 hours of signing up and we'll match your first deposit up to ₦1,000.</p>
            </div>
            <Button size="lg" asChild className="bg-white text-primary hover:bg-white/90">
              <Link to="/register">Claim my credit<ArrowRight className="ml-2 h-4 w-4" /></Link>
            </Button>
          </div>
        </Card>
      </section>

      {/* FAQ */}
      <section className="container mx-auto px-4 pb-16">
        <SectionHead eyebrow="FAQ" title="Frequently asked questions" centered />
        <div className="mx-auto mt-8 max-w-3xl">
          <Accordion type="single" collapsible className="w-full">
            {[
              { q: "How quickly do I receive my order?", a: "Most digital deliveries are instant once payment clears. 2nd number activations may take up to 5 minutes." },
              { q: "What if the account stops working?", a: "Every order is covered by a 24-hour replacement guarantee. Open a support ticket from your dashboard." },
              { q: "Which payment methods are supported?", a: "Card, bank transfer, USSD, and wallet balance — all processed by Paystack." },
              { q: "Can I sell on BluesMarketplace?", a: "Listings are currently curated. Apply to become a verified seller via your dashboard and our admins will review." },
              { q: "How does the wallet work?", a: "Deposit once, then check out instantly with zero card friction. Unused balance can be withdrawn anytime." },
            ].map((item, i) => (
              <AccordionItem key={i} value={`item-${i}`}>
                <AccordionTrigger className="text-left">{item.q}</AccordionTrigger>
                <AccordionContent className="text-muted-foreground">{item.a}</AccordionContent>
              </AccordionItem>
            ))}
          </Accordion>
        </div>
      </section>

      {/* NEWSLETTER */}
      <section className="border-t bg-muted/30">
        <div className="container mx-auto grid items-center gap-8 px-4 py-16 md:grid-cols-2">
          <div>
            <Badge variant="secondary" className="mb-3">Newsletter</Badge>
            <h3 className="text-2xl font-bold tracking-tight md:text-3xl">Be first to know about new drops</h3>
            <p className="mt-2 text-muted-foreground">One short email every week. Hot listings, price drops, and exclusive launches.</p>
          </div>
          <form onSubmit={subscribe} className="flex w-full gap-2">
            <div className="relative flex-1">
              <Mail className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input type="email" required placeholder="you@example.com" value={email} onChange={(e) => setEmail(e.target.value)} className="pl-9" />
            </div>
            <Button type="submit">Subscribe</Button>
          </form>
        </div>
      </section>

      <SiteFooter />
    </div>
  );
}

function SectionHead({
  eyebrow, title, subtitle, centered, small, icon: Icon,
}: { eyebrow?: string; title: string; subtitle?: string; centered?: boolean; small?: boolean; icon?: any }) {
  return (
    <div className={centered ? "mx-auto max-w-2xl text-center" : ""}>
      {eyebrow && (
        <div className={`flex items-center gap-2 ${centered ? "justify-center" : ""}`}>
          {Icon && <Icon className="h-4 w-4 text-accent" />}
          <span className="text-xs font-semibold uppercase tracking-wider text-accent">{eyebrow}</span>
        </div>
      )}
      <h2 className={`mt-2 font-bold tracking-tight ${small ? "text-xl" : "text-2xl md:text-3xl"}`}>{title}</h2>
      {subtitle && <p className="mt-2 text-sm text-muted-foreground md:text-base">{subtitle}</p>}
    </div>
  );
}
