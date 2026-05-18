import { Link } from "@tanstack/react-router";
import { Anchor } from "lucide-react";

export function SiteFooter() {
  return (
    <footer className="border-t border-border/60 bg-muted/30">
      <div className="container mx-auto flex flex-col items-center justify-between gap-4 px-4 py-8 md:flex-row">
        <div className="flex items-center gap-2 text-sm text-muted-foreground">
          <Anchor className="h-4 w-4 text-accent" />
          <span>© {new Date().getFullYear()} BluesMarketplace. All rights reserved.</span>
        </div>
        <div className="flex items-center gap-4 text-sm text-muted-foreground">
          <Link to="/marketplace" className="hover:text-foreground">Marketplace</Link>
          <Link to="/login" className="hover:text-foreground">Sign in</Link>
          <Link to="/register" className="hover:text-foreground">Register</Link>
        </div>
      </div>
    </footer>
  );
}
