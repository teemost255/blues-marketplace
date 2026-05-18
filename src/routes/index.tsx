import { createFileRoute, Link } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { ArrowRight, ShieldCheck, Zap, Wallet, Sparkles } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { SiteHeader } from "@/components/SiteHeader";
import { SiteFooter } from "@/components/SiteFooter";
import { AnimatedBlobs } from "@/components/AnimatedBlobs";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/")({
  component: Landing,
  head: () => ({
    meta: [{ title: "BluesMarketplace — Buy & sell digital products" }],
  }),
});

function Landing() {
  const { data: featured } = useQuery({
    queryKey: ["featured-listings"],
    queryFn: async () => {
      const { data, error } = await supabase
        .from("listings")
        .select("id,title,price,category,image_url")
        .eq("is_active", true)
        .order("created_at", { ascending: false })
        .limit(6);
      if (error) throw error;
      return data ?? [];
    },
  });

  return (
    <div className="flex min-h-screen flex-col">
      <SiteHeader />

      {/* Hero */}
      <section className="relative overflow-hidden">
        <div className="absolute inset-0 -z-10 opacity-90" style={{ background: "var(--gradient-hero)" }} />
        <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_right,oklch(0.74_0.08_190/0.3),transparent_60%)]" />
        <AnimatedBlobs withOrbits />
        <div className="container relative mx-auto px-4 py-20 md:py-32">
          <div className="max-w-3xl">
            <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur">
              <Sparkles className="h-3 w-3" />
              Facebook · Instagram · TikTok · 2nd Numbers
            </div>
          <div className="max-w-3xl">
            <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur">
              <Sparkles className="h-3 w-3" />
              Secure digital marketplace · Paystack checkout
            </div>
            <h1 className="text-4xl font-bold tracking-tight text-white md:text-6xl">
              The marketplace for{" "}
              <span className="bg-gradient-to-r from-[oklch(0.74_0.08_190)] to-white bg-clip-text text-transparent">
                digital products
              </span>
            </h1>
            <p className="mt-6 max-w-xl text-lg text-white/80">
              Buy and sell premium digital accounts, software, and services. Fast,
              secure, and trusted by thousands.
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Button size="lg" asChild className="bg-white text-primary hover:bg-white/90">
                <Link to="/marketplace">
                  Browse marketplace
                  <ArrowRight className="ml-2 h-4 w-4" />
                </Link>
              </Button>
              <Button size="lg" variant="outline" asChild className="border-white/30 bg-white/10 text-white hover:bg-white/20 hover:text-white">
                <Link to="/register">Create free account</Link>
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="container mx-auto px-4 py-20">
        <div className="grid gap-6 md:grid-cols-3">
          {[
            { icon: ShieldCheck, title: "Secure by default", desc: "Role-based access, RLS, and verified transactions." },
            { icon: Zap, title: "Instant delivery", desc: "Digital products are delivered the moment payment clears." },
            { icon: Wallet, title: "Paystack checkout", desc: "Pay securely with cards, bank, USSD, and more." },
          ].map(({ icon: Icon, title, desc }) => (
            <Card key={title} className="border-border/60 p-6" style={{ boxShadow: "var(--shadow-card)" }}>
              <div className="mb-4 grid h-10 w-10 place-items-center rounded-lg bg-accent/10 text-accent">
                <Icon className="h-5 w-5" />
              </div>
              <h3 className="font-semibold">{title}</h3>
              <p className="mt-2 text-sm text-muted-foreground">{desc}</p>
            </Card>
          ))}
        </div>
      </section>

      {/* Featured */}
      <section className="container mx-auto px-4 pb-24">
        <div className="mb-8 flex items-end justify-between">
          <div>
            <h2 className="text-2xl font-bold tracking-tight md:text-3xl">Featured listings</h2>
            <p className="mt-1 text-sm text-muted-foreground">Fresh drops from verified sellers.</p>
          </div>
          <Button variant="ghost" asChild>
            <Link to="/marketplace">View all<ArrowRight className="ml-1 h-4 w-4" /></Link>
          </Button>
        </div>
        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {(featured ?? []).map((l) => (
            <Link key={l.id} to="/marketplace/$id" params={{ id: l.id }}>
              <Card className="group overflow-hidden border-border/60 transition-all hover:-translate-y-1 hover:shadow-lg">
                <div className="aspect-[16/10] overflow-hidden bg-muted">
                  {l.image_url ? (
                    <img src={l.image_url} alt={l.title} className="h-full w-full object-cover transition-transform group-hover:scale-105" />
                  ) : (
                    <div className="grid h-full w-full place-items-center text-muted-foreground" style={{ background: "var(--gradient-card)" }}>
                      <Sparkles className="h-8 w-8" />
                    </div>
                  )}
                </div>
                <div className="p-4">
                  <div className="text-xs uppercase tracking-wide text-accent">{l.category}</div>
                  <div className="mt-1 line-clamp-1 font-semibold">{l.title}</div>
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

      <SiteFooter />
    </div>
  );
}
