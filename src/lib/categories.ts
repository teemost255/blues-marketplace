export const LISTING_CATEGORIES = ["Facebook", "Instagram", "TikTok", "2nd Numbers"] as const;
export type ListingCategory = typeof LISTING_CATEGORIES[number];
