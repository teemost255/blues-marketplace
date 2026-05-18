import { createFileRoute } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/lib/auth";

export const Route = createFileRoute("/dashboard/profile")({
  component: Profile,
});

function Profile() {
  const { user } = useAuth();
  const [displayName, setDisplayName] = useState("");
  const [newPassword, setNewPassword] = useState("");

  useEffect(() => {
    if (!user) return;
    supabase.from("profiles").select("display_name").eq("id", user.id).maybeSingle().then(({ data }) => {
      setDisplayName(data?.display_name ?? "");
    });
  }, [user]);

  const saveProfile = async () => {
    if (!user) return;
    const { error } = await supabase.from("profiles").update({ display_name: displayName }).eq("id", user.id);
    if (error) return toast.error(error.message);
    toast.success("Profile updated");
  };

  const updatePassword = async () => {
    if (newPassword.length < 6) return toast.error("Password must be at least 6 characters");
    const { error } = await supabase.auth.updateUser({ password: newPassword });
    if (error) return toast.error(error.message);
    setNewPassword("");
    toast.success("Password updated");
  };

  return (
    <div className="p-8 max-w-2xl">
      <h1 className="text-2xl font-bold tracking-tight">Profile</h1>
      <Card className="mt-6 space-y-4 p-6">
        <div>
          <Label>Email</Label>
          <Input value={user?.email ?? ""} disabled className="mt-1.5" />
        </div>
        <div>
          <Label htmlFor="name">Display name</Label>
          <Input id="name" value={displayName} onChange={(e) => setDisplayName(e.target.value)} className="mt-1.5" />
        </div>
        <Button onClick={saveProfile}>Save profile</Button>
      </Card>
      <Card className="mt-6 space-y-4 p-6">
        <h2 className="font-semibold">Change password</h2>
        <div>
          <Label htmlFor="pw">New password</Label>
          <Input id="pw" type="password" value={newPassword} onChange={(e) => setNewPassword(e.target.value)} className="mt-1.5" />
        </div>
        <Button onClick={updatePassword}>Update password</Button>
      </Card>
    </div>
  );
}
