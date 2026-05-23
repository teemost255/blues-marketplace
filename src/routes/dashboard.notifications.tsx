import { createFileRoute, Link } from "@tanstack/react-router";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { Bell, Check, CheckCheck } from "lucide-react";
import { toast } from "sonner";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { api } from "@/lib/api";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/dashboard/notifications")({
  component: Notifications,
});

function Notifications() {
  const { user } = useAuth();
  const qc = useQueryClient();

  const { data } = useQuery({
    queryKey: ["notifications", user?.id],
    enabled: !!user,
    queryFn: async () => {
      return await api.get("/api/notifications");
    },
  });

  const markRead = async (id: string) => {
    await api.put(`/api/notifications/${id}/read`);
    qc.invalidateQueries({ queryKey: ["notifications"] });
  };

  const markAllRead = async () => {
    try {
      await api.put("/api/notifications/read-all");
      toast.success("All read");
      qc.invalidateQueries({ queryKey: ["notifications"] });
    } catch (err: any) {
      toast.error(err.message || "Failed");
    }
  };

  const unread = (data ?? []).filter((n) => !n.read).length;

  return (
    <div className="p-4 sm:p-6 md:p-8">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="flex items-center gap-2 text-2xl font-bold md:text-3xl"><Bell className="h-6 w-6" /> Notifications {unread > 0 && <Badge>{unread}</Badge>}</h1>
        {unread > 0 && <Button size="sm" variant="outline" onClick={markAllRead}><CheckCheck className="mr-2 h-4 w-4" /> Mark all read</Button>}
      </div>

      <Card className="mt-6 p-2 md:p-4">
        {data && data.length > 0 ? (
          <ul className="divide-y">
            {data.map((n) => (
              <li key={n.id} className={`flex items-start gap-3 p-3 ${!n.read ? "bg-accent/5" : ""}`}>
                <div className={`mt-1 h-2 w-2 shrink-0 rounded-full ${!n.read ? "bg-accent" : "bg-transparent"}`} />
                <div className="min-w-0 flex-1">
                  <div className="flex items-center gap-2">
                    <div className="font-medium">{n.title}</div>
                    <Badge variant="outline" className="text-[10px]">{n.type}</Badge>
                  </div>
                  {n.body && <p className="mt-0.5 text-sm text-muted-foreground">{n.body}</p>}
                  <div className="mt-1 text-xs text-muted-foreground">{new Date(n.created_at).toLocaleString()}</div>
                  {n.link && <Link to={n.link as any} className="mt-1 inline-block text-xs font-medium text-accent hover:underline">Open →</Link>}
                </div>
                {!n.read && <Button size="icon" variant="ghost" onClick={() => markRead(n.id)} aria-label="Mark read"><Check className="h-4 w-4" /></Button>}
              </li>
            ))}
          </ul>
        ) : (
          <div className="grid place-items-center p-12 text-center text-sm text-muted-foreground">
            <Bell className="mb-3 h-10 w-10 opacity-30" /> No notifications yet.
          </div>
        )}
      </Card>
    </div>
  );
}
