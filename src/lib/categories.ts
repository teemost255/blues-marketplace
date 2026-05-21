import { supabase } from "@/integrations/supabase/client";

export const LISTING_CATEGORIES = ["Facebook", "Instagram", "TikTok", "2nd Numbers"] as const;
export type ListingCategory = string;

export async function fetchListingCategories() {
  const { data, error } = await supabase
    .from("listing_categories")
    .select("name")
    .order("name");

  if (error) throw error;
  return (data ?? []).map((row: any) => row.name);
}
