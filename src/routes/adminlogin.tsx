import { createFileRoute } from "@tanstack/react-router";
import { LoginForm } from "@/routes/login";
import { AdminLoginGuard } from "@/lib/admin-guard";

export const Route = createFileRoute("/adminlogin")({
  component: AdminLogin,
  head: () => ({ meta: [{ title: "Admin sign in — BluesMarketplace" }] }),
});

function AdminLogin() {
  return (
    <AdminLoginGuard>
      <LoginForm
        adminOnly
        title="Admin sign in"
        subtitle="Enter your admin credentials to access the dashboard."
      />
    </AdminLoginGuard>
  );
}
