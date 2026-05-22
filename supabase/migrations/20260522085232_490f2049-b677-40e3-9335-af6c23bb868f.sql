
CREATE OR REPLACE FUNCTION public.validate_listing_category()
RETURNS trigger
LANGUAGE plpgsql
SET search_path = public
AS $function$
begin
  if not exists (select 1 from public.listing_categories where lower(name) = lower(new.category)) then
    raise exception 'Invalid category: %. Allowed categories must be created by admin.', new.category;
  end if;
  return new;
end;
$function$;
