
-- tg_set_updated_at also needs explicit search_path
create or replace function public.tg_set_updated_at()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin new.updated_at = now(); return new; end $$;

-- Revoke direct EXECUTE so these can only run via RLS / triggers
revoke execute on function public.has_role(uuid, public.app_role) from public, anon, authenticated;
revoke execute on function public.handle_new_user() from public, anon, authenticated;
revoke execute on function public.tg_set_updated_at() from public, anon, authenticated;
