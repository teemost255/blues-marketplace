import { createFileRoute, Link } from "@tanstack/react-router";
import { SiteFooter } from "@/components/SiteFooter";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

export const Route = createFileRoute("/terms")({
  component: TermsPage,
  head: () => ({ meta: [{ title: "Terms & Conditions — BluesMarketplace" }] }),
});

function TermsPage() {
  return (
    <div className="min-h-screen bg-background text-foreground">
      <div className="container mx-auto px-4 py-16">
        <div className="max-w-4xl space-y-6">
          <div>
            <p className="text-sm uppercase tracking-[0.2em] text-muted-foreground">Terms</p>
            <h1 className="mt-2 text-4xl font-bold tracking-tight">Terms & Conditions</h1>
            <p className="mt-4 text-base leading-7 text-muted-foreground">
              These terms govern your use of BluesMarketplace. Please read them carefully before creating an account or using the service.
            </p>
          </div>

          <Card className="space-y-4 p-8">
            <section className="space-y-3">
              <h2 className="text-xl font-semibold">Acceptance of terms</h2>
              <p className="text-sm text-muted-foreground">
                By using BluesMarketplace, you agree to follow these rules and to be held accountable for any actions taken on your account.
              </p>
            </section>
            <section className="space-y-3">
              <h2 className="text-xl font-semibold">Account responsibilities</h2>
              <p className="text-sm text-muted-foreground">
                Keep your credentials safe, use accurate information, and comply with applicable laws in your region.
              </p>
            </section>
            <section className="space-y-3">
              <h2 className="text-xl font-semibold">Prohibited behavior</h2>
              <p className="text-sm text-muted-foreground">
                Do not post fraudulent listings, breach marketplace rules, or misuse support systems. Violations may result in suspension.
              </p>
            </section>
            <section className="space-y-3">
              <h2 className="text-xl font-semibold">Changes to the service</h2>
              <p className="text-sm text-muted-foreground">
                We may update these terms as the product evolves. Important changes will be communicated through the platform.
              </p>
            </section>
          </Card>

          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <Link to="/privacy" className="text-sm text-accent hover:underline">Read our Privacy Policy</Link>
            <Button asChild variant="secondary"><Link to="/">Back home</Link></Button>
          </div>
        </div>
      </div>
      <SiteFooter />
    </div>
  );
}
