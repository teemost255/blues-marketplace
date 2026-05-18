import { createFileRoute, Link } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { Activity, Heart, ShoppingBag, Sparkles, TrendingUp, Wallet } from "lucide-react";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";
import { LISTING_CATEGORIES } from "@/lib/categories";

export const Route = createFileRoute("/dashboard/")({
  component: DashboardOverview,
});

function DashboardOverview() {
  const { user } = useAuth();
  const { data } = useQuery({
    queryKey: ["my-stats", user?.id],
    enabled: !!user,
    queryFn: async () => {
      const { data: purchases } = await supabase
        .from("purchases")
        .select("amount,status,created_at,listing_id")
        .eq("user_id", user!.id)
        .order("created_at", { ascending: false });
      const list = purchases ?? [];
      const total = list.reduce((s, p) => s + Number(p.amount), 0);
      const completed = list.filter((p) => p.status === "completed").length;
      const pending = list.filter((p) => p.status === "pending").length;
      return { count: list.length, total, completed, pending, recent: list.slice(0, 5) };
    },
  });

  const { data: featured } = useQuery({
    queryKey: ["recommended"],
    queryFn: async () => {
      const { data } = await supabase
        .from("listings")
        .select("id,title,price,category,image_url")
        .eq("is_active", true)
        .order("created_at", { ascending: false })
        .limit(3);
      return data ?? [];
    },
  });

  return (
    <div className="p-4 sm:p-6 md:p-8">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold tracking-tight md:text-3xl">Welcome back 👋</h1>
          <p className="mt-1 text-sm text-muted-foreground">Here's a quick look at your activity.</p>
        </div>
        <Button asChild size="sm"><Link to="/marketplace"><Sparkles className="mr-2 h-4 w-4" /> Browse marketplace</Link></Button>
      </div>

      <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard icon={ShoppingBag} label="Orders" value={data?.count ?? 0} />
        <StatCard icon={Wallet} label="Total spent" value={`₦${(data?.total ?? 0).toLocaleString()}`} />
        <StatCard icon={Activity} label="Completed" value={data?.completed ?? 0} accent="success" />
        <StatCard icon={TrendingUp} label="Pending" value={data?.pending ?? 0} accent="muted" />
      </div>

      <div className="mt-8 grid gap-4 lg:grid-cols-3">
        <Card className="p-5 lg:col-span-2">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="font-semibold">Recent activity</h2>
            <Button variant="ghost" size="sm" asChild><Link to="/dashboard/orders">View all</Link></Button>
          </div>
          {data?.recent && data.recent.length > 0 ? (
            <ul className="divide-y">
              {data.recent.map((p, i) => (
                <li key={i} className="flex items-center justify-between py-3 text-sm">
                  <div className="flex items-center gap-3">
                    <div className="grid h-9 w-9 place-items-center rounded-full bg-accent/10 text-accent"><ShoppingBag className="h-4 w-4" /></div>
                    <div>
                      <div className="font-medium">Order</div>
                      <div className="text-xs text-muted-foreground">{new Date(p.created_at).toLocaleString()}</div>
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <Badge variant={p.status === "completed" ? "default" : "secondary"}>{p.status}</Badge>
                    <span className="font-medium">₦{Number(p.amount).toLocaleString()}</span>
                  </div>
                </li>
              ))}
            </ul>
          ) : (
            <div className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">No orders yet — start exploring the marketplace.</div>
          )}
        </Card>

        <Card className="p-5">
          <h2 className="mb-4 font-semibold">Shortcuts</h2>
          <div className="grid grid-cols-2 gap-2">
            {LISTING_CATEGORIES.map((c) => (
              <Link key={c} to="/marketplace" className="rounded-lg border bg-muted/30 p-3 text-sm transition-colors hover:bg-secondary">
                <div className="font-medium">{c}</div>
                <div className="mt-0.5 text-xs text-muted-foreground">Browse</div>
              </Link>
            ))}
          </div>
          <div className="mt-4 rounded-lg border border-accent/30 bg-accent/5 p-3">
            <div className="flex items-center gap-2 text-sm font-medium"><Heart className="h-4 w-4 text-accent" /> Tip</div>
            <p className="mt-1 text-xs text-muted-foreground">Complete your profile to unlock faster checkout.</p>
            <Button variant="link" size="sm" asChild className="px-0"><Link to="/dashboard/profile">Update profile →</Link></Button>
          </div>
        </Card>
      </div>

      <div className="mt-8">
        <div className="mb-3 flex items-center justify-between">
          <h2 className="font-semibold">Recommended for you</h2>
          <Button variant="ghost" size="sm" asChild><Link to="/marketplace">See more</Link></Button>
        </div>
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {(featured ?? []).map((l) => (
            <Link key={l.id} to="/marketplace/$id" params={{ id: l.id }}>
              <Card className="overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md">
                <div className="aspect-[16/9] bg-muted">
                  {l.image_url && <img src={l.image_url} alt={l.title} className="h-full w-full object-cover" />}
                </div>
                <div className="p-4">
                  <div className="text-xs uppercase tracking-wide text-accent">{l.category}</div>
                  <div className="mt-1 line-clamp-1 font-semibold">{l.title}</div>
                  <div className="mt-1 font-bold">₦{Number(l.price).toLocaleString()}</div>
                </div>
              </Card>
            </Link>
          ))}
        </div>
      </div>
    </div>
  );
}

function StatCard({ icon: Icon, label, value, accent }: { icon: any; label: string; value: any; accent?: "success" | "muted" }) {
  return (
    <Card className="p-5">
      <div className="flex items-center gap-2 text-sm text-muted-foreground"><Icon className="h-4 w-4" /> {label}</div>
      <div className={`mt-2 text-3xl font-bold ${accent === "success" ? "text-[oklch(0.62_0.14_160)]" : ""}`}>{value}</div>
    </Card>
  );
}
