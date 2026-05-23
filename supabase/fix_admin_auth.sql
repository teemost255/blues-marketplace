-- ============================================================
-- Run this entire block in Supabase Dashboard → SQL Editor
-- Fixes: pgcrypto not enabled + register_admin function missing
-- ============================================================

-- 1. Enable pgcrypto (required for crypt() used in password hashing)
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- 2. Recreate verify_admin_password now that pgcrypto is available
CREATE OR REPLACE FUNCTION public.verify_admin_password(p_email text, password text)
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
    (admins_users.password_hash = crypt(password, admins_users.password_hash)) AS is_valid
  FROM public.admins_users
  WHERE lower(admins_users.email) = lower(p_email)
    AND admins_users.is_active = true;
END;
$$;

GRANT EXECUTE ON FUNCTION public.verify_admin_password(text, text) TO anon, authenticated;

-- 3. Create register_admin function
CREATE OR REPLACE FUNCTION public.register_admin(
  p_email text,
  p_password text,
  p_display_name text DEFAULT NULL
)
RETURNS TABLE(id uuid, email text, display_name text, success boolean, error_message text)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
DECLARE
  v_id uuid;
  v_email text;
  v_display_name text;
BEGIN
  p_email := lower(trim(p_email));

  IF p_password IS NULL OR length(trim(p_password)) < 6 THEN
    RETURN QUERY SELECT NULL::uuid, p_email, NULL::text, false, 'Password must be at least 6 characters'::text;
    RETURN;
  END IF;

  IF EXISTS (SELECT 1 FROM public.admins_users WHERE lower(admins_users.email) = p_email) THEN
    RETURN QUERY SELECT NULL::uuid, p_email, NULL::text, false, 'An admin with this email already exists'::text;
    RETURN;
  END IF;

  INSERT INTO public.admins_users (email, password_hash, display_name)
  VALUES (p_email, crypt(p_password, gen_salt('bf', 4)), p_display_name)
  RETURNING admins_users.id, admins_users.email, admins_users.display_name
  INTO v_id, v_email, v_display_name;

  RETURN QUERY SELECT v_id, v_email, v_display_name, true, NULL::text;
END;
$$;

GRANT EXECUTE ON FUNCTION public.register_admin(text, text, text) TO anon, authenticated;
