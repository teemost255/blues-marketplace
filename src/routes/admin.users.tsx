import { createFileRoute } from "@tanstack/react-router";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { toast } from "sonner";
import { ShieldCheck, ShieldOff } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/admin/users")({
  component: AdminUsers,
});

function AdminUsers() {
  const qc = useQueryClient();
  const { data } = useQuery({
    queryKey: ["admin-users"],
    queryFn: async () => {
      const { data: profiles } = await supabase.from("profiles").select("id,display_name,created_at").order("created_at", { ascending: false });
      const { data: roles } = await supabase.from("user_roles").select("user_id,role");
      const roleMap = new Map<string, string[]>();
      (roles ?? []).forEach((r) => {
        const arr = roleMap.get(r.user_id) ?? [];
        arr.push(r.role);
        roleMap.set(r.user_id, arr);
      });
      return (profiles ?? []).map((p) => ({ ...p, roles: roleMap.get(p.id) ?? [] }));
    },
  });

  const toggleAdmin = async (userId: string, makeAdmin: boolean) => {
    if (makeAdmin) {
      const { error } = await supabase.from("user_roles").insert({ user_id: userId, role: "admin" });
      if (error) return toast.error(error.message);
    } else {
      const { error } = await supabase.from("user_roles").delete().eq("user_id", userId).eq("role", "admin");
      if (error) return toast.error(error.message);
    }
    toast.success("Role updated");
    qc.invalidateQueries({ queryKey: ["admin-users"] });
  };

  return (
    <div className="p-8">
      <h1 className="text-2xl font-bold tracking-tight">Users</h1>
      <div className="mt-6 space-y-3">
        {(data ?? []).map((u: any) => {
          const isAdmin = u.roles.includes("admin");
          return (
            <Card key={u.id} className="flex items-center gap-4 p-4">
              <div className="flex-1 min-w-0">
                <div className="font-medium truncate">{u.display_name ?? "Unnamed"}</div>
                <div className="text-xs text-muted-foreground">Joined {new Date(u.created_at).toLocaleDateString()}</div>
              </div>
              <Badge variant={isAdmin ? "default" : "secondary"}>{isAdmin ? "admin" : "user"}</Badge>
              <Button variant="outline" size="sm" onClick={() => toggleAdmin(u.id, !isAdmin)}>
                {isAdmin ? <><ShieldOff className="mr-1 h-4 w-4" /> Revoke admin</> : <><ShieldCheck className="mr-1 h-4 w-4" /> Make admin</>}
              </Button>
            </Card>
          );
        })}
        {(!data || data.length === 0) && (
          <div className="rounded-lg border border-dashed p-12 text-center text-muted-foreground">No users yet.</div>
        )}
      </div>
    </div>
  );
}
