-- Function to register a new admin user
-- SECURITY DEFINER allows it to bypass RLS on admins_users
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
  -- Normalise email
  p_email := lower(trim(p_email));

  -- Reject blank password
  IF p_password IS NULL OR length(trim(p_password)) < 6 THEN
    RETURN QUERY SELECT NULL::uuid, p_email, NULL::text, false, 'Password must be at least 6 characters'::text;
    RETURN;
  END IF;

  -- Check if email already exists
  IF EXISTS (SELECT 1 FROM public.admins_users WHERE lower(admins_users.email) = p_email) THEN
    RETURN QUERY SELECT NULL::uuid, p_email, NULL::text, false, 'An admin with this email already exists'::text;
    RETURN;
  END IF;

  -- Insert new admin with bcrypt-hashed password
  INSERT INTO public.admins_users (email, password_hash, display_name)
  VALUES (p_email, crypt(p_password, gen_salt('bf', 4)), p_display_name)
  RETURNING admins_users.id, admins_users.email, admins_users.display_name
  INTO v_id, v_email, v_display_name;

  RETURN QUERY SELECT v_id, v_email, v_display_name, true, NULL::text;
END;
$$;

-- Allow anon and authenticated roles to call this function
GRANT EXECUTE ON FUNCTION public.register_admin(text, text, text) TO anon, authenticated;
