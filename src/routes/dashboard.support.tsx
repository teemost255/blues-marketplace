import { createFileRoute } from "@tanstack/react-router";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import { LifeBuoy, MessageCircle, Plus } from "lucide-react";
import { toast } from "sonner";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { api } from "@/lib/api";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/dashboard/support")({
  component: Support,
});

const STATUS_COLOR: Record<string, string> = {
  open: "bg-blue-500/10 text-blue-500",
  in_progress: "bg-amber-500/10 text-amber-500",
  resolved: "bg-emerald-500/10 text-emerald-500",
  closed: "bg-muted text-muted-foreground",
};

function Support() {
  const { user } = useAuth();
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);
  const [subject, setSubject] = useState("");
  const [message, setMessage] = useState("");
  const [priority, setPriority] = useState("normal");

  const { data: tickets } = useQuery({
    queryKey: ["tickets", user?.id],
    enabled: !!user,
    queryFn: async () => {
      return await api.get("/api/tickets/my");
    },
  });

  const submit = async () => {
    if (subject.trim().length < 3 || message.trim().length < 10) return toast.error("Add a subject and a longer message");
    try {
      await api.post("/api/tickets", { subject: subject.trim(), message: message.trim(), priority });
    } catch (err: any) {
      return toast.error(err.message || "Failed to submit ticket");
    }
    toast.success("Ticket submitted — we'll reply soon.");
    setSubject(""); setMessage(""); setPriority("normal"); setOpen(false);
    qc.invalidateQueries({ queryKey: ["tickets"] });
  };

  return (
    <div className="p-4 sm:p-6 md:p-8">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="flex items-center gap-2 text-2xl font-bold md:text-3xl"><LifeBuoy className="h-6 w-6" /> Support</h1>
          <p className="mt-1 text-sm text-muted-foreground">Open a ticket and our team will respond within 1 hour.</p>
        </div>
        <Dialog open={open} onOpenChange={setOpen}>
          <DialogTrigger asChild><Button><Plus className="mr-2 h-4 w-4" /> New ticket</Button></DialogTrigger>
          <DialogContent>
            <DialogHeader><DialogTitle>New support ticket</DialogTitle></DialogHeader>
            <div className="space-y-3">
              <div><Label>Subject</Label><Input value={subject} onChange={(e) => setSubject(e.target.value)} maxLength={120} className="mt-1.5" /></div>
              <div><Label>Priority</Label>
                <Select value={priority} onValueChange={setPriority}>
                  <SelectTrigger className="mt-1.5"><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="low">Low</SelectItem>
                    <SelectItem value="normal">Normal</SelectItem>
                    <SelectItem value="high">High</SelectItem>
                    <SelectItem value="urgent">Urgent</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div><Label>Message</Label><Textarea value={message} onChange={(e) => setMessage(e.target.value)} rows={5} maxLength={2000} className="mt-1.5" /></div>
            </div>
            <DialogFooter><Button onClick={submit} className="w-full">Submit ticket</Button></DialogFooter>
          </DialogContent>
        </Dialog>
      </div>

      <div className="mt-6 grid gap-4">
        {tickets && tickets.length > 0 ? tickets.map((t) => (
          <Card key={t.id} className="p-5">
            <div className="flex flex-wrap items-start justify-between gap-2">
              <div>
                <div className="font-semibold">{t.subject}</div>
                <div className="mt-0.5 text-xs text-muted-foreground">{new Date(t.created_at).toLocaleString()} · Priority {t.priority}</div>
              </div>
              <Badge className={STATUS_COLOR[t.status]}>{t.status.replace("_", " ")}</Badge>
            </div>
            <p className="mt-3 whitespace-pre-line text-sm text-muted-foreground">{t.message}</p>
            {t.admin_reply && (
              <div className="mt-4 rounded-lg border border-accent/30 bg-accent/5 p-3">
                <div className="mb-1 flex items-center gap-2 text-xs font-medium text-accent"><MessageCircle className="h-3.5 w-3.5" /> Support reply</div>
                <p className="whitespace-pre-line text-sm">{t.admin_reply}</p>
              </div>
            )}
          </Card>
        )) : (
          <Card className="grid place-items-center p-12 text-center text-sm text-muted-foreground">
            <LifeBuoy className="mb-3 h-10 w-10 opacity-30" />
            No tickets yet.
          </Card>
        )}
      </div>
    </div>
  );
}
