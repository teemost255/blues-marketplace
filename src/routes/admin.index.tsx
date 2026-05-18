import { createFileRoute } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { Users, Package, Wallet, ShoppingBag } from "lucide-react";
import { Card } from "@/components/ui/card";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/admin/")({
  component: AdminOverview,
});

function AdminOverview() {
  const { data } = useQuery({
    queryKey: ["admin-stats"],
    queryFn: async () => {
      const [u, l, p] = await Promise.all([
        supabase.from("profiles").select("id", { count: "exact", head: true }),
        supabase.from("listings").select("id", { count: "exact", head: true }),
        supabase.from("purchases").select("amount,status"),
      ]);
      const revenue = (p.data ?? []).filter((x) => x.status === "completed").reduce((s, x) => s + Number(x.amount), 0);
      return { users: u.count ?? 0, listings: l.count ?? 0, purchases: p.data?.length ?? 0, revenue };
    },
  });

  const cards = [
    { label: "Total users", value: data?.users ?? 0, icon: Users },
    { label: "Total listings", value: data?.listings ?? 0, icon: Package },
    { label: "Total purchases", value: data?.purchases ?? 0, icon: ShoppingBag },
    { label: "Total revenue", value: `₦${(data?.revenue ?? 0).toLocaleString()}`, icon: Wallet },
  ];

  return (
    <div className="p-8">
      <h1 className="text-2xl font-bold tracking-tight">Admin overview</h1>
      <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {cards.map((c) => (
          <Card key={c.label} className="p-5">
            <div className="flex items-center gap-2 text-sm text-muted-foreground"><c.icon className="h-4 w-4" />{c.label}</div>
            <div className="mt-2 text-3xl font-bold">{c.value}</div>
          </Card>
        ))}
      </div>
    </div>
  );
}
