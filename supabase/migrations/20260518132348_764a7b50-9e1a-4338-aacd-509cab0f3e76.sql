
-- Roles enum + table (separate from profiles for security)
create type public.app_role as enum ('admin', 'user');

create table public.profiles (
  id uuid primary key references auth.users(id) on delete cascade,
  display_name text,
  avatar_url text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table public.user_roles (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users(id) on delete cascade,
  role public.app_role not null,
  created_at timestamptz not null default now(),
  unique (user_id, role)
);

create table public.listings (
  id uuid primary key default gen_random_uuid(),
  title text not null,
  description text not null,
  price numeric(12,2) not null check (price >= 0),
  category text not null,
  image_url text,
  stock integer not null default 1 check (stock >= 0),
  is_active boolean not null default true,
  created_by uuid references auth.users(id) on delete set null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index listings_category_idx on public.listings(category);
create index listings_active_idx on public.listings(is_active);
create index listings_created_at_idx on public.listings(created_at desc);

create table public.purchases (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users(id) on delete cascade,
  listing_id uuid not null references public.listings(id) on delete restrict,
  amount numeric(12,2) not null,
  paystack_reference text unique,
  status text not null default 'pending' check (status in ('pending','completed','failed')),
  created_at timestamptz not null default now()
);

create index purchases_user_idx on public.purchases(user_id);
create index purchases_listing_idx on public.purchases(listing_id);

-- Security-definer role check (avoids RLS recursion)
create or replace function public.has_role(_user_id uuid, _role public.app_role)
returns boolean
language sql
stable
security definer
set search_path = public
as $$
  select exists (
    select 1 from public.user_roles
    where user_id = _user_id and role = _role
  )
$$;

-- updated_at trigger
create or replace function public.tg_set_updated_at()
returns trigger language plpgsql as $$
begin new.updated_at = now(); return new; end $$;

create trigger profiles_set_updated_at before update on public.profiles
  for each row execute function public.tg_set_updated_at();
create trigger listings_set_updated_at before update on public.listings
  for each row execute function public.tg_set_updated_at();

-- Auto-create profile + default user role on signup
create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  insert into public.profiles (id, display_name, avatar_url)
  values (
    new.id,
    coalesce(new.raw_user_meta_data->>'display_name', new.raw_user_meta_data->>'name', split_part(new.email, '@', 1)),
    new.raw_user_meta_data->>'avatar_url'
  )
  on conflict (id) do nothing;

  insert into public.user_roles (user_id, role)
  values (new.id, 'user')
  on conflict (user_id, role) do nothing;

  return new;
end;
$$;

create trigger on_auth_user_created
  after insert on auth.users
  for each row execute function public.handle_new_user();

-- Enable RLS
alter table public.profiles enable row level security;
alter table public.user_roles enable row level security;
alter table public.listings enable row level security;
alter table public.purchases enable row level security;

-- profiles policies
create policy "profiles_select_own_or_admin" on public.profiles
  for select using (auth.uid() = id or public.has_role(auth.uid(), 'admin'));
create policy "profiles_update_own" on public.profiles
  for update using (auth.uid() = id);
create policy "profiles_admin_update" on public.profiles
  for update using (public.has_role(auth.uid(), 'admin'));

-- user_roles policies
create policy "user_roles_select_own_or_admin" on public.user_roles
  for select using (user_id = auth.uid() or public.has_role(auth.uid(), 'admin'));
create policy "user_roles_admin_all" on public.user_roles
  for all using (public.has_role(auth.uid(), 'admin'))
  with check (public.has_role(auth.uid(), 'admin'));

-- listings policies
create policy "listings_public_read_active" on public.listings
  for select using (is_active = true or public.has_role(auth.uid(), 'admin'));
create policy "listings_admin_insert" on public.listings
  for insert with check (public.has_role(auth.uid(), 'admin'));
create policy "listings_admin_update" on public.listings
  for update using (public.has_role(auth.uid(), 'admin'));
create policy "listings_admin_delete" on public.listings
  for delete using (public.has_role(auth.uid(), 'admin'));

-- purchases policies
create policy "purchases_select_own_or_admin" on public.purchases
  for select using (user_id = auth.uid() or public.has_role(auth.uid(), 'admin'));
create policy "purchases_insert_own" on public.purchases
  for insert with check (user_id = auth.uid());
create policy "purchases_admin_update" on public.purchases
  for update using (public.has_role(auth.uid(), 'admin'));
