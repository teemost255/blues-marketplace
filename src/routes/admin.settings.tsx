import { createFileRoute } from "@tanstack/react-router";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useEffect, useState, useMemo } from "react";
import { toast } from "sonner";
import { Save, Megaphone, Headphones, Sparkles } from "lucide-react";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Switch } from "@/components/ui/switch";
import { Button } from "@/components/ui/button";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/admin/settings")({
  component: AdminSettings,
});

function AdminSettings() {
  const { role, user } = useAuth();
  const qc = useQueryClient();
  const { data } = useQuery({
    queryKey: ["site-settings"],
    queryFn: async () => {
      const { data } = await supabase.from("site_settings").select("key,value");
      const map: Record<string, any> = {};
      (data ?? []).forEach((r) => { map[r.key] = r.value; });
      return map;
    },
  });

  const { data: categories } = useQuery({
    queryKey: ["listing-categories"],
    queryFn: async () => {
      const { data, error } = await supabase.from("listing_categories").select("name").order("name");
      if (error) throw error;
      return (data ?? []).map((row: any) => row.name);
    },
  });

  const [hero, setHero] = useState({ headline: "", subheadline: "" });
  const [ann, setAnn] = useState({ enabled: false, text: "" });
  const [support, setSupport] = useState({ email: "", whatsapp: "" });
  const [newCategory, setNewCategory] = useState("");

  const addCategory = async () => {
    const name = newCategory.trim();
    if (!name) return toast.error("Enter a category name");

    const { error } = await supabase.from("listing_categories").insert({ name, created_by: user?.id });
    if (error) return toast.error(error.message);

    await supabase.from("admin_audit_log").insert({
      actor_id: user!.id,
      action: "create_category",
      target_type: "listing_categories",
      target_id: name,
      meta: { name },
    });

    toast.success("Category created");
    setNewCategory("");
    qc.invalidateQueries({ queryKey: ["listing-categories"] });
  };

  useEffect(() => {
    if (!data) return;
    setHero({ headline: data.hero?.headline ?? "", subheadline: data.hero?.subheadline ?? "" });
    setAnn({ enabled: !!data.announcement?.enabled, text: data.announcement?.text ?? "" });
    setSupport({ email: data.support?.email ?? "", whatsapp: data.support?.whatsapp ?? "" });
  }, [data]);

  if (role !== "admin") {
    return <div className="p-8 text-muted-foreground">Admins only.</div>;
  }

  const save = async (key: string, value: any) => {
    const { error } = await supabase.from("site_settings").upsert({ key, value, updated_at: new Date().toISOString(), updated_by: user?.id });
    if (error) return toast.error(error.message);
    await supabase.from("admin_audit_log").insert({
      actor_id: user!.id, action: "settings_update", target_type: "site_settings", target_id: key, meta: value,
    });
    toast.success("Saved");
    qc.invalidateQueries({ queryKey: ["site-settings"] });
  };

  return (
    <div className="p-4 md:p-8 space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Site settings</h1>
        <p className="text-sm text-muted-foreground">Manage public site copy, banners, and support contacts.</p>
      </div>

      <Card className="p-6">
        <div className="mb-4 flex items-center gap-2"><Sparkles className="h-4 w-4 text-accent" /><h2 className="font-semibold">Hero section</h2></div>
        <div className="grid gap-4">
          <div><Label>Headline</Label><Input value={hero.headline} onChange={(e) => setHero({ ...hero, headline: e.target.value })} /></div>
          <div><Label>Subheadline</Label><Textarea value={hero.subheadline} onChange={(e) => setHero({ ...hero, subheadline: e.target.value })} /></div>
        </div>
        <div className="mt-4"><Button onClick={() => save("hero", hero)}><Save className="mr-2 h-4 w-4" />Save hero</Button></div>
      </Card>

      <Card className="p-6">
        <div className="mb-4 flex items-center gap-2"><Megaphone className="h-4 w-4 text-accent" /><h2 className="font-semibold">Announcement banner</h2></div>
        <div className="grid gap-4">
          <div className="flex items-center gap-3"><Switch checked={ann.enabled} onCheckedChange={(v) => setAnn({ ...ann, enabled: v })} /><Label>Show banner sitewide</Label></div>
          <div><Label>Banner text</Label><Textarea value={ann.text} onChange={(e) => setAnn({ ...ann, text: e.target.value })} placeholder="🎉 We just launched 2nd numbers!" /></div>
        </div>
        <div className="mt-4"><Button onClick={() => save("announcement", ann)}><Save className="mr-2 h-4 w-4" />Save banner</Button></div>
      </Card>

      <Card className="p-6">
        <div className="mb-4 flex items-center gap-2"><Headphones className="h-4 w-4 text-accent" /><h2 className="font-semibold">Support contacts</h2></div>
        <div className="grid gap-4 md:grid-cols-2">
          <div><Label>Support email</Label><Input type="email" value={support.email} onChange={(e) => setSupport({ ...support, email: e.target.value })} /></div>
          <div><Label>WhatsApp / phone</Label><Input value={support.whatsapp} onChange={(e) => setSupport({ ...support, whatsapp: e.target.value })} /></div>
        </div>
        <div className="mt-4"><Button onClick={() => save("support", support)}><Save className="mr-2 h-4 w-4" />Save support</Button></div>
      </Card>

      <Card className="p-6">
        <div className="mb-4 flex items-center gap-2"><Sparkles className="h-4 w-4 text-accent" /><h2 className="font-semibold">Listing categories</h2></div>
        <p className="text-sm text-muted-foreground">Create new listing categories for the marketplace and admin listing form.</p>
        <div className="mt-4 grid gap-4 sm:grid-cols-[1fr_auto]">
          <div>
            <Label htmlFor="new-category">New category</Label>
            <Input
              id="new-category"
              value={newCategory}
              onChange={(e) => setNewCategory(e.target.value)}
              placeholder="e.g. Snapchat"
            />
          </div>
          <Button className="self-end" onClick={addCategory}>Add category</Button>
        </div>
        <div className="mt-4 grid gap-2">
          {categories && categories.length > 0 ? (
            categories.map((category) => (
              <div key={category} className="rounded-lg border border-border/70 bg-muted p-3 text-sm text-muted-foreground">{category}</div>
            ))
          ) : (
            <div className="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">No categories available yet.</div>
          )}
        </div>
      </Card>
    </div>
  );
}
