-- Create admins_users table for separate admin authentication
CREATE TABLE IF NOT EXISTS public.admins_users (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  email text NOT NULL UNIQUE,
  password_hash text NOT NULL,
  display_name text,
  avatar_url text,
  is_active boolean NOT NULL DEFAULT true,
  last_login timestamptz,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

-- Enable RLS
ALTER TABLE public.admins_users ENABLE ROW LEVEL SECURITY;

-- Policies: Only admins can view other admins, only service role can modify
CREATE POLICY "admins_users_admin_select" ON public.admins_users
  FOR SELECT USING (auth.role() = 'service_role');

CREATE POLICY "admins_users_admin_all" ON public.admins_users
  FOR ALL USING (auth.role() = 'service_role')
  WITH CHECK (auth.role() = 'service_role');

-- Create index on email for faster lookups
CREATE INDEX IF NOT EXISTS admins_users_email_idx ON public.admins_users (lower(email));
CREATE INDEX IF NOT EXISTS admins_users_is_active_idx ON public.admins_users (is_active);

-- Create trigger to auto-update updated_at
CREATE TRIGGER admins_users_set_updated_at BEFORE UPDATE ON public.admins_users
  FOR EACH ROW EXECUTE FUNCTION public.tg_set_updated_at();

-- Function to verify admin password (for authentication)
CREATE OR REPLACE FUNCTION public.verify_admin_password(email text, password text)
RETURNS TABLE (id uuid, display_name text, email text, is_valid boolean)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
  RETURN QUERY
  SELECT 
    admins_users.id,
    admins_users.display_name,
    admins_users.email,
    (admins_users.password_hash = crypt(password, admins_users.password_hash)) as is_valid
  FROM public.admins_users
  WHERE lower(admins_users.email) = lower($1)
    AND admins_users.is_active = true;
END;
$$;

-- Function to check if email exists in admins_users (for blocking regular login)
CREATE OR REPLACE FUNCTION public.is_admin_email(email text)
RETURNS boolean
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
  SELECT EXISTS (
    SELECT 1 FROM public.admins_users
    WHERE lower(admins_users.email) = lower($1)
      AND admins_users.is_active = true
  );
$$;

-- Grant execute permissions for authentication functions
GRANT EXECUTE ON FUNCTION public.verify_admin_password(text, text) TO anon, authenticated;
GRANT EXECUTE ON FUNCTION public.is_admin_email(text) TO anon, authenticated;
