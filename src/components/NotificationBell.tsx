import { Link } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { Bell } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { api } from "@/lib/api";
import { useAuth } from "@/lib/auth";

export function NotificationBell() {
  const { user } = useAuth();
  const { data: count } = useQuery({
    queryKey: ["unread-notifs", user?.id],
    enabled: !!user,
    refetchInterval: 30000,
    queryFn: async () => {
      const data = await api.get("/api/notifications/unread-count");
      return data.count ?? 0;
    },
  });
  if (!user) return null;
  return (
    <Button variant="ghost" size="icon" asChild aria-label="Notifications" className="relative">
      <Link to="/dashboard/notifications">
        <Bell className="h-4 w-4" />
        {count && count > 0 ? <Badge className="absolute -right-1 -top-1 h-4 min-w-[16px] justify-center rounded-full px-1 text-[10px]">{count > 9 ? "9+" : count}</Badge> : null}
      </Link>
    </Button>
  );
}
