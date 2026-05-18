
CREATE OR REPLACE FUNCTION public.validate_listing_category()
RETURNS trigger
LANGUAGE plpgsql
SET search_path TO 'public'
AS $function$
begin
  if new.category not in ('Facebook','Instagram','TikTok','2nd Numbers') then
    raise exception 'Invalid category: %. Allowed: Facebook, Instagram, TikTok, 2nd Numbers', new.category;
  end if;
  return new;
end;
$function$;
