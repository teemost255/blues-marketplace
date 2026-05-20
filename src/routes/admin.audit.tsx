import { createFileRoute } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/admin/audit")({
  component: AdminAudit,
});

function AdminAudit() {
  const { data } = useQuery({
    queryKey: ["admin-audit"],
    queryFn: async () => {
      const { data } = await supabase
        .from("admin_audit_log")
        .select("id,actor_id,action,target_type,target_id,meta,created_at")
        .order("created_at", { ascending: false })
        .limit(200);
      return data ?? [];
    },
  });

  return (
    <div className="p-4 md:p-8">
      <h1 className="text-2xl font-bold tracking-tight">Audit log</h1>
      <p className="text-sm text-muted-foreground">Every admin & moderator action, recorded.</p>
      <div className="mt-6 space-y-2">
        {(data ?? []).map((row: any) => (
          <Card key={row.id} className="flex flex-col gap-2 p-4 md:flex-row md:items-center">
            <Badge variant="outline" className="self-start">{row.action}</Badge>
            <div className="flex-1 text-sm">
              <div>
                Actor <span className="font-mono">{row.actor_id.slice(0, 8)}</span>
                {row.target_type && <> · target {row.target_type}/<span className="font-mono">{(row.target_id ?? "").slice(0, 8)}</span></>}
              </div>
              {row.meta && Object.keys(row.meta).length > 0 && (
                <div className="mt-1 text-xs text-muted-foreground">{JSON.stringify(row.meta)}</div>
              )}
            </div>
            <div className="text-xs text-muted-foreground">{new Date(row.created_at).toLocaleString()}</div>
          </Card>
        ))}
        {(!data || data.length === 0) && (
          <div className="rounded-lg border border-dashed p-12 text-center text-muted-foreground">No actions logged yet.</div>
        )}
      </div>
    </div>
  );
}
