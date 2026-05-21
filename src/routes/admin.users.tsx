import { createFileRoute } from "@tanstack/react-router";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import { toast } from "sonner";
import { ShieldCheck, ShieldOff, BadgeCheck, Ban, Pause, Play, Search, UserCog } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/admin/users")({
  component: AdminUsers,
});

type Row = {
  id: string;
  display_name: string | null;
  created_at: string;
  status: string;
  is_verified: boolean;
  suspension_reason: string | null;
  roles: string[];
};

function AdminUsers() {
  const qc = useQueryClient();
  const { user: me } = useAuth();
  const [q, setQ] = useState("");

  const { data } = useQuery({
    queryKey: ["admin-users"],
    queryFn: async (): Promise<Row[]> => {
      const { data: profiles } = await supabase
        .from("profiles")
        .select("id,display_name,created_at,status,is_verified,suspension_reason")
        .order("created_at", { ascending: false });
      const { data: roles } = await supabase.from("user_roles").select("user_id,role");
      const roleMap = new Map<string, string[]>();
      (roles ?? []).forEach((r) => {
        const arr = roleMap.get(r.user_id) ?? [];
        arr.push(r.role);
        roleMap.set(r.user_id, arr);
      });
      return (profiles ?? []).map((p: any) => ({ ...p, roles: roleMap.get(p.id) ?? [] }));
    },
  });

  const audit = async (action: string, targetId: string, meta: Record<string, any> = {}) => {
    if (!me) return;
    await supabase.from("admin_audit_log").insert({
      actor_id: me.id,
      action,
      target_type: "user",
      target_id: targetId,
      meta,
    });
  };

  const refresh = () => qc.invalidateQueries({ queryKey: ["admin-users"] });

  const setRoleFor = async (userId: string, nextRole: "admin" | "moderator" | null) => {
    await supabase.from("user_roles").delete().eq("user_id", userId).in("role", ["admin", "moderator"]);
    if (nextRole) {
      const { error } = await supabase.from("user_roles").insert({ user_id: userId, role: nextRole });
      if (error) return toast.error(error.message);
    }
    await audit("role_set", userId, { role: nextRole ?? "user" });
    toast.success("Role updated");
    refresh();
  };

  const setVerified = async (userId: string, v: boolean) => {
    const { error } = await supabase.from("profiles").update({ is_verified: v }).eq("id", userId);
    if (error) return toast.error(error.message);
    await audit(v ? "verify" : "unverify", userId);
    toast.success(v ? "Verified" : "Verification removed");
    refresh();
  };

  const setStatus = async (userId: string, status: "active" | "suspended" | "banned", reason?: string) => {
    const { error } = await supabase
      .from("profiles")
      .update({ status, suspension_reason: status === "active" ? null : reason ?? null })
      .eq("id", userId);
    if (error) return toast.error(error.message);
    await audit(`status_${status}`, userId, { reason });
    toast.success(`User ${status}`);
    refresh();
  };

  const filtered = (data ?? []).filter((u) =>
    !q ? true : (u.display_name ?? "").toLowerCase().includes(q.toLowerCase()) || u.id.includes(q),
  );

  return (
    <div className="mx-auto max-w-screen-xl p-4 md:p-8">
      <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Users</h1>
          <p className="text-sm text-muted-foreground">Verify, suspend, ban, or assign staff roles.</p>
        </div>
        <div className="relative w-full md:w-80">
          <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            type="search"
            aria-label="Search users"
            placeholder="Search by name or ID"
            value={q}
            onChange={(e) => setQ(e.target.value)}
            className="pl-9"
          />
        </div>
      </div>

      <div className="mt-6 space-y-3">
        {filtered.map((u) => {
          const userIsAdmin = u.roles.includes("admin");
          const userIsMod = u.roles.includes("moderator");
          const statusVariant = u.status === "active" ? "secondary" : u.status === "suspended" ? "outline" : "destructive";
          return (
            <Card key={u.id} className="flex flex-col gap-3 p-4 md:flex-row md:items-center md:gap-4">
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2">
                  <div className="font-medium truncate">{u.display_name ?? "Unnamed"}</div>
                  {u.is_verified && <BadgeCheck className="h-4 w-4 text-accent" />}
                </div>
                <div className="text-xs text-muted-foreground">
                  ID {u.id.slice(0, 8)} · Joined {new Date(u.created_at).toLocaleDateString()}
                  {u.suspension_reason ? ` · ${u.suspension_reason}` : ""}
                </div>
              </div>
              <div className="flex flex-wrap items-center gap-2">
                <Badge variant={userIsAdmin ? "default" : userIsMod ? "outline" : "secondary"}>
                  {userIsAdmin ? "admin" : userIsMod ? "moderator" : "user"}
                </Badge>
                <Badge variant={statusVariant as any}>{u.status}</Badge>
              </div>
              <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                <Button variant="outline" size="sm" className="w-full sm:w-auto" onClick={() => setVerified(u.id, !u.is_verified)}>
                  <BadgeCheck className="mr-1 h-4 w-4" /> {u.is_verified ? "Unverify" : "Verify"}
                </Button>
                {u.status === "active" ? (
                  <SuspendDialog onConfirm={(reason) => setStatus(u.id, "suspended", reason)} />
                ) : (
                  <Button variant="outline" size="sm" className="w-full sm:w-auto" onClick={() => setStatus(u.id, "active")}>
                    <Play className="mr-1 h-4 w-4" /> Reactivate
                  </Button>
                )}
                <Button
                  variant="outline" size="sm"
                  className="w-full text-destructive hover:text-destructive sm:w-auto"
                  onClick={() => {
                    if (confirm(`Ban ${u.display_name ?? "this user"}? They will lose access immediately.`)) {
                      setStatus(u.id, "banned", "Banned by staff");
                    }
                  }}
                >
                  <Ban className="mr-1 h-4 w-4" /> Ban
                </Button>
                <RoleMenu
                    current={userIsAdmin ? "admin" : userIsMod ? "moderator" : "user"}
                    onChange={(r) => setRoleFor(u.id, r === "user" ? null : r)}
                  />
              </div>
            </Card>
          );
        })}
        {filtered.length === 0 && (
          <div className="rounded-lg border border-dashed p-12 text-center text-muted-foreground">No users match.</div>
        )}
      </div>
    </div>
  );
}

function SuspendDialog({ onConfirm, disabled }: { onConfirm: (reason: string) => void; disabled?: boolean }) {
  const [open, setOpen] = useState(false);
  const [reason, setReason] = useState("");
  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" size="sm" disabled={disabled}><Pause className="mr-1 h-4 w-4" /> Suspend</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader><DialogTitle>Suspend user</DialogTitle></DialogHeader>
        <div className="space-y-4">
          <Label htmlFor="suspend-reason">Reason (shown in audit log)</Label>
          <Textarea
            id="suspend-reason"
            required
            value={reason}
            onChange={(e) => setReason(e.target.value)}
            placeholder="Policy violation, suspicious activity, etc."
          />
        </div>
        <DialogFooter>
          <Button variant="outline" onClick={() => { setOpen(false); setReason(""); }}>Cancel</Button>
          <Button
            disabled={!reason.trim()}
            onClick={() => {
              if (!reason.trim()) return;
              onConfirm(reason.trim());
              setOpen(false);
              setReason("");
            }}
          >
            Suspend
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}

function RoleMenu({ current, onChange }: { current: "admin" | "moderator" | "user"; onChange: (r: "admin" | "moderator" | "user") => void }) {
  const [open, setOpen] = useState(false);
  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" size="sm"><UserCog className="mr-1 h-4 w-4" /> Role</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader><DialogTitle>Assign role</DialogTitle></DialogHeader>
        <div className="grid gap-2">
          {(["admin", "moderator", "user"] as const).map((r) => (
            <Button key={r} variant={current === r ? "default" : "outline"} onClick={() => { onChange(r); setOpen(false); }} className="justify-start">
              {r === "admin" && <ShieldCheck className="mr-2 h-4 w-4" />}
              {r === "moderator" && <ShieldOff className="mr-2 h-4 w-4" />}
              {r === "user" && <UserCog className="mr-2 h-4 w-4" />}
              {r}
            </Button>
          ))}
        </div>
      </DialogContent>
    </Dialog>
  );
}
