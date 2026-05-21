import { createFileRoute, Link } from "@tanstack/react-router";
import { SiteFooter } from "@/components/SiteFooter";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

export const Route = createFileRoute("/privacy")({
  component: PrivacyPage,
  head: () => ({ meta: [{ title: "Privacy Policy — BluesMarketplace" }] }),
});

function PrivacyPage() {
  return (
    <div className="min-h-screen bg-background text-foreground">
      <div className="container mx-auto px-4 py-16">
        <div className="max-w-4xl space-y-6">
          <div>
            <p className="text-sm uppercase tracking-[0.2em] text-muted-foreground">Privacy</p>
            <h1 className="mt-2 text-4xl font-bold tracking-tight">Privacy Policy</h1>
            <p className="mt-4 text-base leading-7 text-muted-foreground">
              At BluesMarketplace, we take your privacy seriously. This policy describes how we collect, use, and protect your information.
            </p>
          </div>

          <Card className="space-y-4 p-8">
            <section className="space-y-3">
              <h2 className="text-xl font-semibold">Information we collect</h2>
              <p className="text-sm text-muted-foreground">
                We collect the details you provide when you sign up, purchase, or communicate with support, including email, display name, and payment metadata.
              </p>
            </section>
            <section className="space-y-3">
              <h2 className="text-xl font-semibold">How we use it</h2>
              <p className="text-sm text-muted-foreground">
                Your data powers account access, order history, support tickets, and personalized marketplace listings. We never sell your information to third parties.
              </p>
            </section>
            <section className="space-y-3">
              <h2 className="text-xl font-semibold">Security</h2>
              <p className="text-sm text-muted-foreground">
                We protect your account and transactional data using modern security practices, and we only retain information needed to operate the service.
              </p>
            </section>
            <section className="space-y-3">
              <h2 className="text-xl font-semibold">Cookies & tracking</h2>
              <p className="text-sm text-muted-foreground">
                We use cookies and similar technologies to improve the app, remember your preferences, and keep sessions secure.
              </p>
            </section>
          </Card>

          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <Link to="/terms" className="text-sm text-accent hover:underline">Read our Terms & Conditions</Link>
            <Button asChild variant="secondary"><Link to="/">Back home</Link></Button>
          </div>
        </div>
      </div>
      <SiteFooter />
    </div>
  );
}
