-- Migrate existing admin users from auth.users to admins_users
-- Note: For security, use a different password or reset admin passwords after this migration
-- This preserves the admin user records but moves them to the dedicated admins_users table

INSERT INTO public.admins_users (email, password_hash, display_name, created_at, updated_at)
SELECT 
  auth_users.email,
  crypt(auth_users.email, gen_salt('bf', 4)), -- Generate new hash with email as temp password
  profiles.display_name,
  auth_users.created_at,
  auth_users.updated_at
FROM auth.users auth_users
INNER JOIN public.user_roles ur ON auth_users.id = ur.user_id AND ur.role = 'admin'
LEFT JOIN public.profiles profiles ON auth_users.id = profiles.id
WHERE auth_users.email NOT IN (SELECT email FROM public.admins_users)
ON CONFLICT (email) DO NOTHING;

-- Remove admin role from user_roles to complete the migration
-- Admins are now separate and won't be in the regular user_roles table
DELETE FROM public.user_roles 
WHERE user_id IN (
  SELECT id FROM auth.users 
  WHERE email IN (SELECT email FROM public.admins_users)
)
AND role = 'admin';

-- Add index for quick lookups during cleanup
CREATE INDEX IF NOT EXISTS user_roles_user_id_role_idx ON public.user_roles (user_id, role);
