
ALTER TABLE public.profiles
  ADD COLUMN IF NOT EXISTS status text NOT NULL DEFAULT 'active',
  ADD COLUMN IF NOT EXISTS is_verified boolean NOT NULL DEFAULT false,
  ADD COLUMN IF NOT EXISTS suspended_until timestamptz,
  ADD COLUMN IF NOT EXISTS suspension_reason text;

DO $$ BEGIN
  ALTER TABLE public.profiles
    ADD CONSTRAINT profiles_status_check CHECK (status IN ('active','suspended','banned'));
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

CREATE TABLE IF NOT EXISTS public.site_settings (
  key text PRIMARY KEY,
  value jsonb NOT NULL DEFAULT '{}'::jsonb,
  updated_at timestamptz NOT NULL DEFAULT now(),
  updated_by uuid
);
ALTER TABLE public.site_settings ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS site_settings_public_read ON public.site_settings;
CREATE POLICY site_settings_public_read ON public.site_settings FOR SELECT USING (true);

DROP POLICY IF EXISTS site_settings_admin_write ON public.site_settings;
CREATE POLICY site_settings_admin_write ON public.site_settings
  FOR ALL USING (public.has_role(auth.uid(),'admin'::app_role))
  WITH CHECK (public.has_role(auth.uid(),'admin'::app_role));

CREATE TABLE IF NOT EXISTS public.admin_audit_log (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  actor_id uuid NOT NULL,
  action text NOT NULL,
  target_type text,
  target_id text,
  meta jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now()
);
ALTER TABLE public.admin_audit_log ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS admin_audit_admin_read ON public.admin_audit_log;
CREATE POLICY admin_audit_admin_read ON public.admin_audit_log
  FOR SELECT USING (public.has_role(auth.uid(),'admin'::app_role));

DROP POLICY IF EXISTS admin_audit_staff_insert ON public.admin_audit_log;
CREATE POLICY admin_audit_staff_insert ON public.admin_audit_log
  FOR INSERT WITH CHECK (
    public.has_role(auth.uid(),'admin'::app_role)
    OR public.has_role(auth.uid(),'moderator'::app_role)
  );

DROP POLICY IF EXISTS profiles_moderator_select ON public.profiles;
CREATE POLICY profiles_moderator_select ON public.profiles
  FOR SELECT USING (public.has_role(auth.uid(),'moderator'::app_role));

DROP POLICY IF EXISTS profiles_moderator_update ON public.profiles;
CREATE POLICY profiles_moderator_update ON public.profiles
  FOR UPDATE USING (public.has_role(auth.uid(),'moderator'::app_role));

DROP POLICY IF EXISTS listings_moderator_insert ON public.listings;
CREATE POLICY listings_moderator_insert ON public.listings
  FOR INSERT WITH CHECK (public.has_role(auth.uid(),'moderator'::app_role));

DROP POLICY IF EXISTS listings_moderator_update ON public.listings;
CREATE POLICY listings_moderator_update ON public.listings
  FOR UPDATE USING (public.has_role(auth.uid(),'moderator'::app_role));

INSERT INTO public.site_settings (key, value) VALUES
  ('hero', '{"headline":"The trusted marketplace for digital accounts","subheadline":"Buy verified accounts and second numbers from vetted sellers."}'::jsonb),
  ('announcement', '{"enabled":false,"text":""}'::jsonb),
  ('support', '{"email":"support@bluesmarketplace.com","whatsapp":""}'::jsonb)
ON CONFLICT (key) DO NOTHING;
