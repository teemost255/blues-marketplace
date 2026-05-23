import { createFileRoute } from "@tanstack/react-router";
import { useMemo, useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { api } from "@/lib/api";

export const Route = createFileRoute("/admin/audit")({
  component: AdminAudit,
});

function AdminAudit() {
  const [search, setSearch] = useState("");
  const [action, setAction] = useState("");

  const { data } = useQuery({
    queryKey: ["admin-audit"],
    queryFn: async () => {
      return await api.get("/api/admin/audit");
    },
  });

  const auditRows = useMemo(() => {
    return (data ?? []).filter((row: any) => {
      const normalizedSearch = search.trim().toLowerCase();
      if (action && row.action !== action) return false;
      if (!normalizedSearch) return true;
      return [row.action, row.actor_id, row.target_type, row.target_id, JSON.stringify(row.meta || "")]
        .join(" ")
        .toLowerCase()
        .includes(normalizedSearch);
    });
  }, [action, data, search]);

  const auditActions = Array.from(new Set((data ?? []).map((row: any) => row.action))).sort();

  return (
    <div className="p-4 md:p-8">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Audit log</h1>
          <p className="text-sm text-muted-foreground">Every admin & moderator action, recorded.</p>
        </div>
        <div className="grid w-full gap-3 sm:max-w-md sm:grid-cols-2">
          <div>
            <label className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">Filter action</label>
            <Select value={action} onValueChange={(value) => setAction(value)}>
              <SelectTrigger className="mt-1.5"><SelectValue placeholder="All actions" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="">All actions</SelectItem>
                {auditActions.map((actionName) => (
                  <SelectItem key={actionName} value={actionName}>{actionName}</SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div>
            <Label htmlFor="audit-search">Search</Label>
            <Input
              id="audit-search"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search actor, target, meta..."
              className="mt-1.5"
            />
          </div>
        </div>
      </div>

      <div className="mt-6 space-y-2">
        {auditRows.map((row: any) => (
          <Card key={row.id} className="flex flex-col gap-2 p-4 md:flex-row md:items-center">
            <Badge variant="outline" className="self-start">{row.action}</Badge>
            <div className="flex-1 text-sm">
              <div>
                Actor <span className="font-mono">{row.actor_id.slice(0, 8)}</span>
                {row.target_type && (
                  <>
                    {' '}· target {row.target_type}/<span className="font-mono">{(row.target_id ?? "").slice(0, 8)}</span>
                  </>
                )}
              </div>
              {row.meta && Object.keys(row.meta).length > 0 && (
                <div className="mt-1 text-xs text-muted-foreground">{JSON.stringify(row.meta)}</div>
              )}
            </div>
            <div className="text-xs text-muted-foreground">{new Date(row.created_at).toLocaleString()}</div>
          </Card>
        ))}
        {auditRows.length === 0 && (
          <div className="rounded-lg border border-dashed p-12 text-center text-muted-foreground">No actions match your filter.</div>
        )}
      </div>
    </div>
  );
}
