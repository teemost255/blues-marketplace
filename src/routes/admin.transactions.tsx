import { createFileRoute } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/admin/transactions")({
  component: AdminTransactions,
});

function AdminTransactions() {
  const { data } = useQuery({
    queryKey: ["admin-transactions"],
    queryFn: async () => {
      const { data, error } = await supabase
        .from("purchases")
        .select("id,amount,status,created_at,user_id,listing:listings(title)")
        .order("created_at", { ascending: false })
        .limit(100);
      if (error) throw error;
      return data ?? [];
    },
  });

  return (
    <div className="p-8">
      <h1 className="text-2xl font-bold tracking-tight">Transactions</h1>
      <div className="mt-6 space-y-3">
        {(data ?? []).map((t: any) => (
          <Card key={t.id} className="flex items-center gap-4 p-4">
            <div className="flex-1 min-w-0">
              <div className="font-medium truncate">{t.listing?.title ?? "Listing"}</div>
              <div className="text-xs text-muted-foreground">User {t.user_id.slice(0, 8)} · {new Date(t.created_at).toLocaleString()}</div>
            </div>
            <div className="font-semibold">₦{Number(t.amount).toLocaleString()}</div>
            <Badge variant={t.status === "completed" ? "default" : "secondary"}>{t.status}</Badge>
          </Card>
        ))}
        {(!data || data.length === 0) && (
          <div className="rounded-lg border border-dashed p-12 text-center text-muted-foreground">No transactions yet.</div>
        )}
      </div>
    </div>
  );
}
