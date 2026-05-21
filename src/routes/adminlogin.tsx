import { createFileRoute } from "@tanstack/react-router";
import { LoginForm } from "@/routes/login";

export const Route = createFileRoute("/adminlogin")({
  component: AdminLogin,
  head: () => ({ meta: [{ title: "Admin sign in — BluesMarketplace" }] }),
});

function AdminLogin() {
  return (
    <LoginForm
      adminOnly
      title="Admin sign in"
      subtitle="Enter your admin credentials to access the dashboard."
    />
  );
}
