import { createFileRoute } from "@tanstack/react-router";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import { toast } from "sonner";
import { Pencil, Plus, Trash2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogFooter } from "@/components/ui/dialog";
import { Switch } from "@/components/ui/switch";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/admin/listings")({
  component: AdminListings,
});

type Listing = {
  id?: string;
  title: string;
  description: string;
  price: number;
  category: string;
  image_url: string;
  stock: number;
  is_active: boolean;
};

const blank: Listing = { title: "", description: "", price: 0, category: "Accounts", image_url: "", stock: 1, is_active: true };

function AdminListings() {
  const qc = useQueryClient();
  const { user } = useAuth();
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState<Listing>(blank);

  const { data } = useQuery({
    queryKey: ["admin-listings"],
    queryFn: async () => {
      const { data, error } = await supabase.from("listings").select("*").order("created_at", { ascending: false });
      if (error) throw error;
      return data ?? [];
    },
  });

  const openNew = () => { setForm(blank); setOpen(true); };
  const openEdit = (l: any) => { setForm({ ...l }); setOpen(true); };

  const save = async () => {
    const payload = { ...form, price: Number(form.price), stock: Number(form.stock), created_by: user?.id };
    if (form.id) {
      const { id, ...update } = payload as any;
      const { error } = await supabase.from("listings").update(update).eq("id", form.id);
      if (error) return toast.error(error.message);
    } else {
      const { error } = await supabase.from("listings").insert(payload);
      if (error) return toast.error(error.message);
    }
    toast.success("Saved");
    setOpen(false);
    qc.invalidateQueries({ queryKey: ["admin-listings"] });
  };

  const remove = async (id: string) => {
    if (!confirm("Delete this listing?")) return;
    const { error } = await supabase.from("listings").delete().eq("id", id);
    if (error) return toast.error(error.message);
    toast.success("Deleted");
    qc.invalidateQueries({ queryKey: ["admin-listings"] });
  };

  return (
    <div className="p-8">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold tracking-tight">Listings</h1>
        <Dialog open={open} onOpenChange={setOpen}>
          <DialogTrigger asChild>
            <Button onClick={openNew}><Plus className="mr-1 h-4 w-4" /> New listing</Button>
          </DialogTrigger>
          <DialogContent className="max-w-lg">
            <DialogHeader><DialogTitle>{form.id ? "Edit" : "Create"} listing</DialogTitle></DialogHeader>
            <div className="space-y-3">
              <div><Label>Title</Label><Input value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} /></div>
              <div><Label>Description</Label><Textarea rows={4} value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} /></div>
              <div className="grid grid-cols-2 gap-3">
                <div><Label>Price (₦)</Label><Input type="number" value={form.price} onChange={(e) => setForm({ ...form, price: Number(e.target.value) })} /></div>
                <div><Label>Stock</Label><Input type="number" value={form.stock} onChange={(e) => setForm({ ...form, stock: Number(e.target.value) })} /></div>
              </div>
              <div><Label>Category</Label><Input value={form.category} onChange={(e) => setForm({ ...form, category: e.target.value })} /></div>
              <div><Label>Image URL</Label><Input value={form.image_url} onChange={(e) => setForm({ ...form, image_url: e.target.value })} /></div>
              <div className="flex items-center gap-2"><Switch checked={form.is_active} onCheckedChange={(v) => setForm({ ...form, is_active: v })} /><Label>Active</Label></div>
            </div>
            <DialogFooter><Button onClick={save}>Save</Button></DialogFooter>
          </DialogContent>
        </Dialog>
      </div>

      <div className="mt-6 space-y-3">
        {(data ?? []).map((l: any) => (
          <Card key={l.id} className="flex items-center gap-4 p-4">
            <div className="h-14 w-20 flex-shrink-0 overflow-hidden rounded bg-muted">
              {l.image_url && <img src={l.image_url} alt="" className="h-full w-full object-cover" />}
            </div>
            <div className="flex-1 min-w-0">
              <div className="font-medium truncate">{l.title}</div>
              <div className="text-xs text-muted-foreground">{l.category} · {l.is_active ? "active" : "inactive"} · stock {l.stock}</div>
            </div>
            <div className="font-semibold">₦{Number(l.price).toLocaleString()}</div>
            <Button variant="ghost" size="icon" onClick={() => openEdit(l)}><Pencil className="h-4 w-4" /></Button>
            <Button variant="ghost" size="icon" onClick={() => remove(l.id)}><Trash2 className="h-4 w-4" /></Button>
          </Card>
        ))}
        {(!data || data.length === 0) && (
          <div className="rounded-lg border border-dashed p-12 text-center text-muted-foreground">No listings yet. Create one to get started.</div>
        )}
      </div>
    </div>
  );
}
