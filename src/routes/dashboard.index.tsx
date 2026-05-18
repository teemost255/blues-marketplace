import { createFileRoute, Link } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { ShoppingBag, Wallet, Sparkles } from "lucide-react";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/dashboard/")({
  component: DashboardOverview,
});

function DashboardOverview() {
  const { user } = useAuth();
  const { data } = useQuery({
    queryKey: ["my-stats", user?.id],
    enabled: !!user,
    queryFn: async () => {
      const { data: purchases } = await supabase.from("purchases").select("amount,status").eq("user_id", user!.id);
      const total = (purchases ?? []).reduce((s, p) => s + Number(p.amount), 0);
      return { count: purchases?.length ?? 0, total };
    },
  });

  return (
    <div className="p-8">
      <h1 className="text-2xl font-bold tracking-tight">Welcome back 👋</h1>
      <p className="mt-1 text-muted-foreground">Here's a quick look at your activity.</p>
      <div className="mt-6 grid gap-4 sm:grid-cols-3">
        <Card className="p-5">
          <div className="flex items-center gap-2 text-sm text-muted-foreground"><ShoppingBag className="h-4 w-4" /> Orders</div>
          <div className="mt-2 text-3xl font-bold">{data?.count ?? 0}</div>
        </Card>
        <Card className="p-5">
          <div className="flex items-center gap-2 text-sm text-muted-foreground"><Wallet className="h-4 w-4" /> Total spent</div>
          <div className="mt-2 text-3xl font-bold">₦{(data?.total ?? 0).toLocaleString()}</div>
        </Card>
        <Card className="p-5">
          <div className="flex items-center gap-2 text-sm text-muted-foreground"><Sparkles className="h-4 w-4" /> Discover</div>
          <Button asChild className="mt-3 w-full"><Link to="/marketplace">Browse listings</Link></Button>
        </Card>
      </div>
    </div>
  );
}
