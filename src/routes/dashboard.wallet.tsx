import { createFileRoute } from "@tanstack/react-router";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import { ArrowDownLeft, ArrowUpRight, Wallet as WalletIcon, Plus } from "lucide-react";
import { toast } from "sonner";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogFooter } from "@/components/ui/dialog";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/dashboard/wallet")({
  component: WalletPage,
});

function WalletPage() {
  const { user } = useAuth();
  const qc = useQueryClient();
  const [amount, setAmount] = useState("");
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);

  const { data: wallet } = useQuery({
    queryKey: ["wallet", user?.id],
    enabled: !!user,
    queryFn: async () => {
      const { data } = await supabase.from("wallets").select("balance").eq("user_id", user!.id).maybeSingle();
      return data ?? { balance: 0 };
    },
  });

  const { data: txs } = useQuery({
    queryKey: ["wtx", user?.id],
    enabled: !!user,
    queryFn: async () => {
      const { data } = await supabase.from("wallet_transactions").select("*").eq("user_id", user!.id).order("created_at", { ascending: false }).limit(50);
      return data ?? [];
    },
  });

  const deposit = async () => {
    const n = Number(amount);
    if (!n || n < 100) return toast.error("Minimum ₦100");
    setLoading(true);
    const { error } = await supabase.rpc("wallet_deposit_mock", { _amount: n });
    setLoading(false);
    if (error) return toast.error(error.message);
    toast.success(`₦${n.toLocaleString()} added (test mode)`);
    setAmount(""); setOpen(false);
    qc.invalidateQueries({ queryKey: ["wallet"] });
    qc.invalidateQueries({ queryKey: ["wtx"] });
  };

  return (
    <div className="p-4 sm:p-6 md:p-8">
      <h1 className="text-2xl font-bold tracking-tight md:text-3xl">My wallet</h1>
      <p className="mt-1 text-sm text-muted-foreground">Top up with Paystack and pay listings instantly. Test mode until live keys are connected.</p>

      <Card className="mt-6 overflow-hidden border-0 p-0">
        <div className="relative p-6 text-primary-foreground md:p-8" style={{ background: "var(--gradient-hero)" }}>
          <div className="flex items-center gap-2 text-sm opacity-90"><WalletIcon className="h-4 w-4" /> Available balance</div>
          <div className="mt-2 text-4xl font-bold tracking-tight md:text-5xl">₦{Number(wallet?.balance ?? 0).toLocaleString()}</div>
          <div className="mt-5 flex flex-wrap gap-2">
            <Dialog open={open} onOpenChange={setOpen}>
              <DialogTrigger asChild>
                <Button variant="secondary" className="gap-2"><Plus className="h-4 w-4" /> Add money</Button>
              </DialogTrigger>
              <DialogContent>
                <DialogHeader><DialogTitle>Top up wallet</DialogTitle></DialogHeader>
                <p className="text-sm text-muted-foreground">Test mode — using mock Paystack. Funds appear immediately.</p>
                <div>
                  <label className="text-sm font-medium">Amount (₦)</label>
                  <Input type="number" inputMode="numeric" value={amount} onChange={(e) => setAmount(e.target.value)} placeholder="1000" className="mt-1.5" />
                  <div className="mt-3 flex flex-wrap gap-2">
                    {[500, 1000, 5000, 10000].map((v) => (
                      <Button key={v} variant="outline" size="sm" onClick={() => setAmount(String(v))}>₦{v.toLocaleString()}</Button>
                    ))}
                  </div>
                </div>
                <DialogFooter>
                  <Button onClick={deposit} disabled={loading} className="w-full">{loading ? "Processing..." : "Confirm top-up"}</Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>
            <Badge variant="secondary" className="bg-white/15 text-white hover:bg-white/20">Test mode</Badge>
          </div>
        </div>
      </Card>

      <Card className="mt-6 p-4 md:p-6">
        <h2 className="mb-4 font-semibold">Transaction history</h2>
        {txs && txs.length > 0 ? (
          <ul className="divide-y">
            {txs.map((t) => (
              <li key={t.id} className="flex items-center justify-between gap-3 py-3 text-sm">
                <div className="flex min-w-0 items-center gap-3">
                  <div className={`grid h-9 w-9 shrink-0 place-items-center rounded-full ${t.amount > 0 ? "bg-emerald-500/10 text-emerald-500" : "bg-rose-500/10 text-rose-500"}`}>
                    {t.amount > 0 ? <ArrowDownLeft className="h-4 w-4" /> : <ArrowUpRight className="h-4 w-4" />}
                  </div>
                  <div className="min-w-0">
                    <div className="truncate font-medium">{t.description ?? t.type}</div>
                    <div className="text-xs text-muted-foreground">{new Date(t.created_at).toLocaleString()}</div>
                  </div>
                </div>
                <div className={`shrink-0 font-semibold ${t.amount > 0 ? "text-emerald-500" : ""}`}>
                  {t.amount > 0 ? "+" : ""}₦{Number(Math.abs(t.amount)).toLocaleString()}
                </div>
              </li>
            ))}
          </ul>
        ) : (
          <div className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">No transactions yet.</div>
        )}
      </Card>
    </div>
  );
}
