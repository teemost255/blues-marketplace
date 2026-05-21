import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useRef, useState } from "react";
import { toast } from "sonner";
import { Camera, Loader2 } from "lucide-react";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/dashboard/profile")({
  component: Profile,
});

function Profile() {
  const { user } = useAuth();
  const fileRef = useRef<HTMLInputElement>(null);
  const [form, setForm] = useState({ display_name: "", username: "", phone: "", country: "", bio: "", avatar_url: "" });
  const [newPassword, setNewPassword] = useState("");
  const [uploading, setUploading] = useState(false);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (!user) return;
    supabase.from("profiles").select("display_name,username,phone,country,bio,avatar_url").eq("id", user.id).maybeSingle().then(({ data }) => {
      if (data) setForm({
        display_name: data.display_name ?? "", username: data.username ?? "", phone: data.phone ?? "",
        country: data.country ?? "", bio: data.bio ?? "", avatar_url: data.avatar_url ?? "",
      });
    });
  }, [user]);

  const saveProfile = async () => {
    if (!user) return;
    setSaving(true);
    const { error } = await supabase.from("profiles").update(form).eq("id", user.id);
    setSaving(false);
    if (error) return toast.error(error.message);
    toast.success("Profile updated");
  };

  const updatePassword = async () => {
    if (newPassword.length < 6) return toast.error("Password must be at least 6 characters");
    const { error } = await supabase.auth.updateUser({ password: newPassword });
    if (error) return toast.error(error.message);
    setNewPassword(""); toast.success("Password updated");
  };

  const uploadAvatar = async (file: File) => {
    if (!user) return;
    if (file.size > 5 * 1024 * 1024) return toast.error("Max 5MB");
    setUploading(true);
    const path = `${user.id}/${Date.now()}-${file.name.replace(/[^a-zA-Z0-9.-]/g, "_")}`;
    const { error: upErr } = await supabase.storage.from("avatars").upload(path, file, { upsert: true });
    if (upErr) { setUploading(false); return toast.error(upErr.message); }
    const { data: pub } = supabase.storage.from("avatars").getPublicUrl(path);
    const url = pub.publicUrl;
    const { error } = await supabase.from("profiles").update({ avatar_url: url }).eq("id", user.id);
    setUploading(false);
    if (error) return toast.error(error.message);
    setForm((f) => ({ ...f, avatar_url: url }));
    toast.success("Avatar updated");
  };

  const initial = (form.display_name || user?.email || "?").charAt(0).toUpperCase();

  return (
    <div className="max-w-3xl p-4 sm:p-6 md:p-8">
      <h1 className="text-2xl font-bold tracking-tight md:text-3xl">Profile</h1>

      <Card className="mt-6 p-6">
        <div className="flex items-center gap-5">
          <div className="relative">
            <Avatar className="h-20 w-20">
              <AvatarImage src={form.avatar_url} alt={form.display_name} />
              <AvatarFallback className="text-xl">{initial}</AvatarFallback>
            </Avatar>
            <button onClick={() => fileRef.current?.click()} disabled={uploading} className="absolute -bottom-1 -right-1 grid h-7 w-7 place-items-center rounded-full bg-primary text-primary-foreground shadow-md hover:bg-primary/90 disabled:opacity-50" aria-label="Change avatar">
              {uploading ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Camera className="h-3.5 w-3.5" />}
            </button>
            <input ref={fileRef} type="file" accept="image/*" hidden onChange={(e) => e.target.files?.[0] && uploadAvatar(e.target.files[0])} />
          </div>
          <div className="min-w-0">
            <div className="truncate font-semibold">{form.display_name || "Unnamed"}</div>
            <div className="truncate text-sm text-muted-foreground">{user?.email}</div>
          </div>
        </div>
      </Card>

      <Card className="mt-6 space-y-4 p-6">
        <h2 className="font-semibold">Personal details</h2>
        <div className="grid gap-4 sm:grid-cols-2">
          <div><Label>Display name</Label><Input value={form.display_name} onChange={(e) => setForm({ ...form, display_name: e.target.value })} maxLength={80} className="mt-1.5" /></div>
          <div><Label>Username</Label><Input value={form.username} onChange={(e) => setForm({ ...form, username: e.target.value })} maxLength={40} className="mt-1.5" /></div>
          <div><Label>Phone</Label><Input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} maxLength={20} className="mt-1.5" /></div>
          <div><Label>Country</Label><Input value={form.country} onChange={(e) => setForm({ ...form, country: e.target.value })} maxLength={56} className="mt-1.5" /></div>
        </div>
        <div><Label>Bio</Label><Textarea value={form.bio} onChange={(e) => setForm({ ...form, bio: e.target.value })} rows={3} maxLength={500} className="mt-1.5" placeholder="Tell us about yourself" /></div>
        <Button onClick={saveProfile} disabled={saving}>{saving ? "Saving..." : "Save profile"}</Button>
      </Card>

      <Card className="mt-6 space-y-4 p-6">
        <h2 className="font-semibold">Change password</h2>
        <div className="grid gap-4 sm:grid-cols-2">
          <div className="sm:col-span-1"><Label>Email</Label><Input value={user?.email ?? ""} disabled className="mt-1.5" /></div>
          <div className="sm:col-span-1"><Label>New password</Label><Input type="password" value={newPassword} onChange={(e) => setNewPassword(e.target.value)} className="mt-1.5" /></div>
        </div>
        <Button onClick={updatePassword}>Update password</Button>
      </Card>
    </div>
  );
}
