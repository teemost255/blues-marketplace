-- Grant admin role and ensure a profile exists for the specified user
insert into public.profiles (id, display_name, avatar_url)
select id,
  split_part(email, '@', 1),
  null
from auth.users
where email = 'agboolasamul09@gmail.com'
on conflict (id) do nothing;

insert into public.user_roles (user_id, role)
select id, 'admin'
from auth.users
where email = 'agboolasamul09@gmail.com'
on conflict (user_id, role) do nothing;
