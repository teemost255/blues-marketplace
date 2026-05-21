import { useQuery } from "@tanstack/react-query";
import { Megaphone, X } from "lucide-react";
import { useState } from "react";
import { supabase } from "@/integrations/supabase/client";

export function AnnouncementBanner() {
  const [dismissed, setDismissed] = useState(false);
  const { data } = useQuery({
    queryKey: ["announcement"],
    queryFn: async () => {
      const { data } = await supabase.from("site_settings").select("value").eq("key", "announcement").maybeSingle();
      return (data?.value ?? {}) as { enabled?: boolean; text?: string };
    },
  });

  if (dismissed || !data?.enabled || !data?.text) return null;
  return (
    <div className="relative bg-accent text-accent-foreground">
      <div className="container mx-auto flex items-center gap-2 px-4 py-2 pr-10 text-sm">
        <Megaphone className="h-4 w-4 shrink-0" />
        <span className="truncate">{data.text}</span>
      </div>
      <button onClick={() => setDismissed(true)} aria-label="Dismiss" className="absolute right-3 top-1/2 -translate-y-1/2 rounded p-1 hover:bg-black/10">
        <X className="h-3.5 w-3.5" />
      </button>
    </div>
  );
}
