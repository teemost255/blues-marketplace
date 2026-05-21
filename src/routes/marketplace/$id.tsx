import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import { ArrowLeft, Heart, ShieldCheck, Sparkles, Wallet, Zap } from "lucide-react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { SiteHeader } from "@/components/SiteHeader";
import { SiteFooter } from "@/components/SiteFooter";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/marketplace/$id")({
  component: ListingDetail,
});

function ListingDetail() {
  const { id } = Route.useParams();
  const { user } = useAuth();
  const navigate = useNavigate();
  const qc = useQueryClient();
  const [buying, setBuying] = useState(false);

  const { data: listing, isLoading } = useQuery({
    queryKey: ["listing", id],
    queryFn: async () => {
      const { data, error } = await supabase.from("listings").select("*").eq("id", id).maybeSingle();
      if (error) throw error;
      return data;
    },
  });

  const { data: wallet } = useQuery({
    queryKey: ["wallet", user?.id],
    enabled: !!user,
    queryFn: async () => {
      const { data } = await supabase.from("wallets").select("balance").eq("user_id", user!.id).maybeSingle();
      return data ?? { balance: 0 };
    },
  });

  const { data: wished } = useQuery({
    queryKey: ["wished", user?.id, id],
    enabled: !!user,
    queryFn: async () => {
      const { data } = await supabase.from("wishlists").select("id").eq("user_id", user!.id).eq("listing_id", id).maybeSingle();
      return data;
    },
  });

  const toggleWish = async () => {
    if (!user) return navigate({ to: "/login" });
    if (wished) {
      await supabase.from("wishlists").delete().eq("id", wished.id);
      toast.success("Removed from wishlist");
    } else {
      await supabase.from("wishlists").insert({ user_id: user.id, listing_id: id });
      toast.success("Saved to wishlist");
    }
    qc.invalidateQueries({ queryKey: ["wished"] });
    qc.invalidateQueries({ queryKey: ["wishlist"] });
  };

  const handleBuy = async () => {
    if (!user) { toast.info("Sign in to purchase"); navigate({ to: "/login" }); return; }
    if (!listing) return;
    if (Number(wallet?.balance ?? 0) < Number(listing.price)) {
      toast.error("Insufficient wallet balance — top up to continue");
      navigate({ to: "/dashboard/wallet" });
      return;
    }
    setBuying(true);
    const { error } = await supabase.rpc("wallet_checkout", { _listing_id: listing.id });
    setBuying(false);
    if (error) return toast.error(error.message);
    toast.success("Purchase complete!");
    qc.invalidateQueries({ queryKey: ["wallet"] });
    navigate({ to: "/dashboard/orders" });
  };

  if (isLoading) {
    return <div className="flex min-h-screen flex-col"><SiteHeader /><div className="container mx-auto px-4 py-16">Loading…</div><SiteFooter /></div>;
  }
  if (!listing) {
    return (
      <div className="flex min-h-screen flex-col"><SiteHeader />
        <div className="container mx-auto px-4 py-16 text-center">
          <h1 className="text-2xl font-semibold">Listing not found</h1>
          <Button asChild className="mt-4"><Link to="/marketplace">Back to marketplace</Link></Button>
        </div><SiteFooter />
      </div>
    );
  }

  const insufficient = user && Number(wallet?.balance ?? 0) < Number(listing.price);

  return (
    <div className="flex min-h-screen flex-col">
      <SiteHeader />
      <main className="container mx-auto flex-1 px-4 py-10">
        <Link to="/marketplace" className="mb-6 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
          <ArrowLeft className="h-4 w-4" /> Back to marketplace
        </Link>
        <div className="grid gap-8 lg:grid-cols-[1.4fr_1fr]">
          <Card className="overflow-hidden">
            <div className="aspect-[16/10] bg-muted">
              {listing.image_url ? <img src={listing.image_url} alt={listing.title} className="h-full w-full object-cover" /> : (
                <div className="grid h-full w-full place-items-center text-muted-foreground" style={{ background: "var(--gradient-card)" }}><Sparkles className="h-12 w-12" /></div>
              )}
            </div>
            <div className="p-6">
              <div className="flex flex-wrap items-center gap-2">
                <Badge variant="secondary">{listing.category}</Badge>
                <Button variant="ghost" size="sm" onClick={toggleWish} className="ml-auto gap-1">
                  <Heart className={`h-4 w-4 ${wished ? "fill-rose-500 text-rose-500" : ""}`} /> {wished ? "Saved" : "Save"}
                </Button>
              </div>
              <h1 className="mt-3 text-3xl font-bold tracking-tight">{listing.title}</h1>
              <p className="mt-4 whitespace-pre-line text-muted-foreground">{listing.description}</p>
            </div>
          </Card>

          <div className="space-y-4">
            <Card className="p-6">
              <div className="text-sm text-muted-foreground">Price</div>
              <div className="mt-1 text-4xl font-bold">₦{Number(listing.price).toLocaleString()}</div>
              <div className="mt-2 text-sm text-muted-foreground">{listing.stock > 0 ? `${listing.stock} in stock` : "Out of stock"}</div>
              {user && (
                <div className="mt-3 flex items-center justify-between rounded-md bg-muted/50 px-3 py-2 text-xs">
                  <span className="flex items-center gap-1 text-muted-foreground"><Wallet className="h-3.5 w-3.5" /> Wallet</span>
                  <span className="font-semibold">₦{Number(wallet?.balance ?? 0).toLocaleString()}</span>
                </div>
              )}
              <Button size="lg" className="mt-4 w-full" disabled={listing.stock === 0 || buying} onClick={handleBuy}>
                {buying ? "Processing..." : insufficient ? "Top up wallet" : "Buy with wallet"}
              </Button>
              <p className="mt-3 text-center text-xs text-muted-foreground">Secure escrow · Instant delivery</p>
            </Card>
            <Card className="space-y-3 p-6 text-sm">
              <div className="flex items-center gap-2"><ShieldCheck className="h-4 w-4 text-accent" /> Verified seller</div>
              <div className="flex items-center gap-2"><Zap className="h-4 w-4 text-accent" /> Instant digital delivery</div>
              <div className="flex items-center gap-2"><Sparkles className="h-4 w-4 text-accent" /> Buyer protection</div>
            </Card>
          </div>
        </div>
      </main>
      <SiteFooter />
    </div>
  );
}
