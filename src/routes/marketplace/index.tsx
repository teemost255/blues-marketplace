import { createFileRoute, Link } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { useState, useMemo } from "react";
import { Search, Sparkles } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Card } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { SiteHeader } from "@/components/SiteHeader";
import { SiteFooter } from "@/components/SiteFooter";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/marketplace/")({
  component: Marketplace,
  head: () => ({ meta: [{ title: "Marketplace — BluesMarketplace" }] }),
});

const PAGE_SIZE = 9;

function Marketplace() {
  const [q, setQ] = useState("");
  const [category, setCategory] = useState("all");
  const [sort, setSort] = useState("newest");
  const [page, setPage] = useState(0);

  const { data, isLoading } = useQuery({
    queryKey: ["listings", q, category, sort, page],
    queryFn: async () => {
      let query = supabase.from("listings").select("*", { count: "exact" }).eq("is_active", true);
      if (q) query = query.ilike("title", `%${q}%`);
      if (category !== "all") query = query.eq("category", category);
      if (sort === "newest") query = query.order("created_at", { ascending: false });
      if (sort === "price_asc") query = query.order("price", { ascending: true });
      if (sort === "price_desc") query = query.order("price", { ascending: false });
      query = query.range(page * PAGE_SIZE, page * PAGE_SIZE + PAGE_SIZE - 1);
      const { data, error, count } = await query;
      if (error) throw error;
      return { rows: data ?? [], count: count ?? 0 };
    },
  });

  const categories = useMemo(() => ["all", "Accounts", "Software", "Templates", "Services", "Other"], []);
  const totalPages = data ? Math.max(1, Math.ceil(data.count / PAGE_SIZE)) : 1;

  return (
    <div className="flex min-h-screen flex-col">
      <SiteHeader />
      <main className="container mx-auto flex-1 px-4 py-10">
        <div className="mb-8">
          <h1 className="text-3xl font-bold tracking-tight">Marketplace</h1>
          <p className="mt-1 text-muted-foreground">Browse verified digital products from trusted sellers.</p>
        </div>

        <div className="mb-6 flex flex-wrap gap-3">
          <div className="relative flex-1 min-w-[200px]">
            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input placeholder="Search listings..." className="pl-9" value={q} onChange={(e) => { setQ(e.target.value); setPage(0); }} />
          </div>
          <Select value={category} onValueChange={(v) => { setCategory(v); setPage(0); }}>
            <SelectTrigger className="w-[180px]"><SelectValue /></SelectTrigger>
            <SelectContent>{categories.map((c) => <SelectItem key={c} value={c}>{c === "all" ? "All categories" : c}</SelectItem>)}</SelectContent>
          </Select>
          <Select value={sort} onValueChange={setSort}>
            <SelectTrigger className="w-[180px]"><SelectValue /></SelectTrigger>
            <SelectContent>
              <SelectItem value="newest">Newest first</SelectItem>
              <SelectItem value="price_asc">Price: low to high</SelectItem>
              <SelectItem value="price_desc">Price: high to low</SelectItem>
            </SelectContent>
          </Select>
        </div>

        {isLoading ? (
          <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: 6 }).map((_, i) => (
              <Card key={i} className="h-72 animate-pulse bg-muted/40" />
            ))}
          </div>
        ) : data && data.rows.length > 0 ? (
          <>
            <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
              {data.rows.map((l) => (
                <Link key={l.id} to="/marketplace/$id" params={{ id: l.id }}>
                  <Card className="group overflow-hidden transition-all hover:-translate-y-1 hover:shadow-lg">
                    <div className="aspect-[16/10] overflow-hidden bg-muted">
                      {l.image_url ? (
                        <img src={l.image_url} alt={l.title} className="h-full w-full object-cover transition-transform group-hover:scale-105" />
                      ) : (
                        <div className="grid h-full w-full place-items-center text-muted-foreground" style={{ background: "var(--gradient-card)" }}>
                          <Sparkles className="h-8 w-8" />
                        </div>
                      )}
                    </div>
                    <div className="p-4">
                      <div className="text-xs uppercase tracking-wide text-accent">{l.category}</div>
                      <div className="mt-1 line-clamp-1 font-semibold">{l.title}</div>
                      <div className="mt-1 line-clamp-2 text-sm text-muted-foreground">{l.description}</div>
                      <div className="mt-3 text-lg font-bold">₦{Number(l.price).toLocaleString()}</div>
                    </div>
                  </Card>
                </Link>
              ))}
            </div>
            <div className="mt-8 flex items-center justify-center gap-2">
              <Button variant="outline" size="sm" disabled={page === 0} onClick={() => setPage((p) => p - 1)}>Previous</Button>
              <span className="text-sm text-muted-foreground">Page {page + 1} of {totalPages}</span>
              <Button variant="outline" size="sm" disabled={page + 1 >= totalPages} onClick={() => setPage((p) => p + 1)}>Next</Button>
            </div>
          </>
        ) : (
          <div className="rounded-lg border border-dashed p-16 text-center text-muted-foreground">
            No listings match your search.
          </div>
        )}
      </main>
      <SiteFooter />
    </div>
  );
}
