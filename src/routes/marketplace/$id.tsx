import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeft, ShieldCheck, Sparkles, Zap } from "lucide-react";
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

  const { data: listing, isLoading } = useQuery({
    queryKey: ["listing", id],
    queryFn: async () => {
      const { data, error } = await supabase.from("listings").select("*").eq("id", id).maybeSingle();
      if (error) throw error;
      return data;
    },
  });

  const handleBuy = async () => {
    if (!user) {
      toast.info("Sign in to purchase");
      navigate({ to: "/login" });
      return;
    }
    if (!listing) return;
    // Record a pending purchase. Paystack flow can be wired here next.
    const { error } = await supabase.from("purchases").insert({
      user_id: user.id,
      listing_id: listing.id,
      amount: listing.price,
      status: "completed", // mock; switch to "pending" when wiring Paystack
    });
    if (error) return toast.error(error.message);
    toast.success("Purchase recorded! View it in your orders.");
    navigate({ to: "/dashboard/orders" });
  };

  if (isLoading) {
    return (
      <div className="flex min-h-screen flex-col">
        <SiteHeader />
        <div className="container mx-auto px-4 py-16">Loading…</div>
        <SiteFooter />
      </div>
    );
  }

  if (!listing) {
    return (
      <div className="flex min-h-screen flex-col">
        <SiteHeader />
        <div className="container mx-auto px-4 py-16 text-center">
          <h1 className="text-2xl font-semibold">Listing not found</h1>
          <Button asChild className="mt-4"><Link to="/marketplace">Back to marketplace</Link></Button>
        </div>
        <SiteFooter />
      </div>
    );
  }

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
              {listing.image_url ? (
                <img src={listing.image_url} alt={listing.title} className="h-full w-full object-cover" />
              ) : (
                <div className="grid h-full w-full place-items-center text-muted-foreground" style={{ background: "var(--gradient-card)" }}>
                  <Sparkles className="h-12 w-12" />
                </div>
              )}
            </div>
            <div className="p-6">
              <Badge variant="secondary" className="mb-3">{listing.category}</Badge>
              <h1 className="text-3xl font-bold tracking-tight">{listing.title}</h1>
              <p className="mt-4 whitespace-pre-line text-muted-foreground">{listing.description}</p>
            </div>
          </Card>

          <div className="space-y-4">
            <Card className="p-6">
              <div className="text-sm text-muted-foreground">Price</div>
              <div className="mt-1 text-4xl font-bold">₦{Number(listing.price).toLocaleString()}</div>
              <div className="mt-2 text-sm text-muted-foreground">{listing.stock > 0 ? `${listing.stock} in stock` : "Out of stock"}</div>
              <Button size="lg" className="mt-6 w-full" disabled={listing.stock === 0} onClick={handleBuy}>
                Buy now
              </Button>
              <p className="mt-3 text-center text-xs text-muted-foreground">Secure checkout · Instant delivery</p>
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
