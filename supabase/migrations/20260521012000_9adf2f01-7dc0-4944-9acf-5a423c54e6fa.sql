create table if not exists public.listing_categories (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  created_by uuid references auth.users(id) on delete set null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create unique index if not exists listing_categories_name_unique_idx on public.listing_categories (lower(name));

insert into public.listing_categories (name)
values
  ('Facebook'),
  ('Instagram'),
  ('TikTok'),
  ('2nd Numbers')
on conflict (lower(name)) do nothing;

create or replace function public.validate_listing_category()
returns trigger
language plpgsql
as $function$
begin
  if not exists (select 1 from public.listing_categories where lower(name) = lower(new.category)) then
    raise exception 'Invalid category: %. Allowed categories must be created by admin.', new.category;
  end if;
  return new;
end;
$function$;

drop trigger if exists listings_validate_category on public.listings;
create trigger listings_validate_category
  before insert or update on public.listings
  for each row execute function public.validate_listing_category();

update public.listings set category = 'Facebook'
  where lower(category) not in ('facebook', 'instagram', 'tiktok', '2nd numbers');
