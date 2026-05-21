import { createFileRoute, Link } from "@tanstack/react-router";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import { LifeBuoy, MessageCircle } from "lucide-react";
import { toast } from "sonner";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/admin/tickets")({
  component: AdminTickets,
});

function AdminTickets() {
  const { loading } = useAuth();
  const qc = useQueryClient();
  const [replyOpen, setReplyOpen] = useState<string | null>(null);
  const [reply, setReply] = useState("");

  const { data } = useQuery({
    queryKey: ["admin-tickets"],
    queryFn: async () => {
      const { data } = await supabase.from("support_tickets").select("*").order("created_at", { ascending: false }).limit(200);
      return data ?? [];
    },
  });

  if (loading) return <div className="p-8 text-muted-foreground">Loading...</div>;

  const sendReply = async (id: string, status: string) => {
    const { error } = await supabase.from("support_tickets").update({ admin_reply: reply, status }).eq("id", id);
    if (error) return toast.error(error.message);
    const t = (data ?? []).find((x) => x.id === id);
    if (t) await supabase.from("notifications").insert({ user_id: t.user_id, title: "Support reply on your ticket", body: t.subject, type: "info", link: "/dashboard/support" });
    toast.success("Reply sent");
    setReplyOpen(null); setReply("");
    qc.invalidateQueries({ queryKey: ["admin-tickets"] });
  };

  const setStatus = async (id: string, status: string) => {
    const { error } = await supabase.from("support_tickets").update({ status }).eq("id", id);
    if (error) return toast.error(error.message);
    qc.invalidateQueries({ queryKey: ["admin-tickets"] });
  };

  return (
    <div className="p-6 md:p-8">
      <h1 className="flex items-center gap-2 text-2xl font-bold"><LifeBuoy className="h-6 w-6" /> Support tickets</h1>
      <div className="mt-6 space-y-4">
        {(data ?? []).map((t) => (
          <Card key={t.id} className="p-5">
            <div className="flex flex-wrap items-start justify-between gap-2">
              <div className="min-w-0">
                <div className="font-semibold">{t.subject}</div>
                <div className="text-xs text-muted-foreground">{new Date(t.created_at).toLocaleString()} · Priority {t.priority}</div>
              </div>
              <Select value={t.status} onValueChange={(v) => setStatus(t.id, v)}>
                <SelectTrigger className="w-[160px]"><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="open">Open</SelectItem>
                  <SelectItem value="in_progress">In progress</SelectItem>
                  <SelectItem value="resolved">Resolved</SelectItem>
                  <SelectItem value="closed">Closed</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <p className="mt-3 whitespace-pre-line text-sm text-muted-foreground">{t.message}</p>
            {t.admin_reply && (
              <div className="mt-3 rounded-lg border border-accent/30 bg-accent/5 p-3 text-sm"><MessageCircle className="mr-1 inline h-3.5 w-3.5" /> {t.admin_reply}</div>
            )}
            {replyOpen === t.id ? (
              <div className="mt-4 space-y-2">
                <Textarea rows={3} value={reply} onChange={(e) => setReply(e.target.value)} placeholder="Type a reply..." />
                <div className="flex gap-2">
                  <Button size="sm" onClick={() => sendReply(t.id, "in_progress")}>Send reply</Button>
                  <Button size="sm" variant="outline" onClick={() => sendReply(t.id, "resolved")}>Send & resolve</Button>
                  <Button size="sm" variant="ghost" onClick={() => { setReplyOpen(null); setReply(""); }}>Cancel</Button>
                </div>
              </div>
            ) : (
              <Button size="sm" variant="outline" className="mt-3" onClick={() => { setReplyOpen(t.id); setReply(t.admin_reply ?? ""); }}>Reply</Button>
            )}
          </Card>
        ))}
        {(!data || data.length === 0) && <Card className="grid place-items-center p-12 text-sm text-muted-foreground">No tickets yet.</Card>}
      </div>
    </div>
  );
}
