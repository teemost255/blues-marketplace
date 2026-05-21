import { createFileRoute, Link } from "@tanstack/react-router";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { Heart, Trash2, Sparkles } from "lucide-react";
import { toast } from "sonner";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/dashboard/wishlist")({
  component: Wishlist,
});

function Wishlist() {
  const { user } = useAuth();
  const qc = useQueryClient();

  const { data } = useQuery({
    queryKey: ["wishlist", user?.id],
    enabled: !!user,
    queryFn: async () => {
      const { data: w } = await supabase.from("wishlists").select("id,listing_id,created_at").eq("user_id", user!.id);
      if (!w?.length) return [];
      const ids = w.map((x) => x.listing_id);
      const { data: listings } = await supabase.from("listings").select("id,title,price,category,image_url,is_active").in("id", ids);
      return (w ?? []).map((x) => ({ ...x, listing: listings?.find((l) => l.id === x.listing_id) }));
    },
  });

  const remove = async (id: string) => {
    const { error } = await supabase.from("wishlists").delete().eq("id", id);
    if (error) return toast.error(error.message);
    toast.success("Removed from wishlist");
    qc.invalidateQueries({ queryKey: ["wishlist"] });
  };

  return (
    <div className="p-4 sm:p-6 md:p-8">
      <h1 className="flex items-center gap-2 text-2xl font-bold tracking-tight md:text-3xl"><Heart className="h-6 w-6 text-rose-500" /> Wishlist</h1>
      <p className="mt-1 text-sm text-muted-foreground">Saved listings for later.</p>

      {data && data.length > 0 ? (
        <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {data.map((item) => (
            <Card key={item.id} className="overflow-hidden">
              {item.listing ? (
                <>
                  <Link to="/marketplace/$id" params={{ id: item.listing.id }}>
                    <div className="aspect-[16/10] bg-muted">
                      {item.listing.image_url ? <img src={item.listing.image_url} alt={item.listing.title} className="h-full w-full object-cover" /> : <div className="grid h-full w-full place-items-center text-muted-foreground" style={{ background: "var(--gradient-card)" }}><Sparkles /></div>}
                    </div>
                  </Link>
                  <div className="space-y-2 p-4">
                    <Badge variant="secondary">{item.listing.category}</Badge>
                    <div className="font-semibold line-clamp-1">{item.listing.title}</div>
                    <div className="font-bold">₦{Number(item.listing.price).toLocaleString()}</div>
                    <div className="flex gap-2 pt-2">
                      <Button asChild size="sm" className="flex-1"><Link to="/marketplace/$id" params={{ id: item.listing.id }}>View</Link></Button>
                      <Button variant="outline" size="icon" onClick={() => remove(item.id)} aria-label="Remove"><Trash2 className="h-4 w-4" /></Button>
                    </div>
                  </div>
                </>
              ) : (
                <div className="p-6 text-sm text-muted-foreground">Listing unavailable <Button size="sm" variant="ghost" onClick={() => remove(item.id)}>Remove</Button></div>
              )}
            </Card>
          ))}
        </div>
      ) : (
        <Card className="mt-6 grid place-items-center p-12 text-center text-sm text-muted-foreground">
          <Heart className="mb-3 h-10 w-10 opacity-30" />
          Your wishlist is empty. Tap the heart icon on any listing to save it.
          <Button asChild size="sm" className="mt-4"><Link to="/marketplace">Browse marketplace</Link></Button>
        </Card>
      )}
    </div>
  );
}
