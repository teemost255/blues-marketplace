import { createFileRoute } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/dashboard/orders")({
  component: Orders,
});

function Orders() {
  const { user } = useAuth();
  const { data } = useQuery({
    queryKey: ["my-orders", user?.id],
    enabled: !!user,
    queryFn: async () => {
      const { data, error } = await supabase
        .from("purchases")
        .select("id,amount,status,created_at,listing:listings(id,title,image_url,category)")
        .eq("user_id", user!.id)
        .order("created_at", { ascending: false });
      if (error) throw error;
      return data ?? [];
    },
  });

  return (
    <div className="p-8">
      <h1 className="text-2xl font-bold tracking-tight">My orders</h1>
      <div className="mt-6 space-y-3">
        {(data ?? []).map((o: any) => (
          <Card key={o.id} className="flex items-center gap-4 p-4">
            <div className="h-16 w-24 flex-shrink-0 overflow-hidden rounded bg-muted">
              {o.listing?.image_url && <img src={o.listing.image_url} alt="" className="h-full w-full object-cover" />}
            </div>
            <div className="flex-1 min-w-0">
              <div className="font-medium truncate">{o.listing?.title ?? "Listing"}</div>
              <div className="text-xs text-muted-foreground">{new Date(o.created_at).toLocaleString()}</div>
            </div>
            <div className="text-right">
              <div className="font-semibold">₦{Number(o.amount).toLocaleString()}</div>
              <Badge variant={o.status === "completed" ? "default" : "secondary"} className="mt-1">{o.status}</Badge>
            </div>
          </Card>
        ))}
        {(!data || data.length === 0) && (
          <div className="rounded-lg border border-dashed p-12 text-center text-muted-foreground">No orders yet.</div>
        )}
      </div>
    </div>
  );
}
