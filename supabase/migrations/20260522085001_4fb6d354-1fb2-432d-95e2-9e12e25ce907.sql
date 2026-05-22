
-- 1. Enable RLS on listing_categories
ALTER TABLE public.listing_categories ENABLE ROW LEVEL SECURITY;

CREATE POLICY "listing_categories_public_read"
  ON public.listing_categories FOR SELECT
  USING (true);

CREATE POLICY "listing_categories_admin_write"
  ON public.listing_categories FOR ALL
  TO authenticated
  USING (public.has_role(auth.uid(), 'admin'))
  WITH CHECK (public.has_role(auth.uid(), 'admin'));

-- 2. Fix search_path on is_admin_or_moderator
CREATE OR REPLACE FUNCTION public.is_admin_or_moderator()
RETURNS boolean
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
  SELECT EXISTS (
    SELECT 1 FROM public.user_roles
    WHERE user_id = auth.uid() AND role IN ('admin','moderator')
  );
$$;

-- 3. Revoke verify_admin_password from anon and public
REVOKE EXECUTE ON FUNCTION public.verify_admin_password(text, text) FROM PUBLIC;
REVOKE EXECUTE ON FUNCTION public.verify_admin_password(text, text) FROM anon;
REVOKE EXECUTE ON FUNCTION public.verify_admin_password(text, text) FROM authenticated;
