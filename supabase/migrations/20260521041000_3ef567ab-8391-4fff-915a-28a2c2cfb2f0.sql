-- Strict RBAC helper and hardened admin-only policies

CREATE OR REPLACE FUNCTION public.is_admin_or_moderator()
RETURNS boolean
LANGUAGE sql
STABLE
SECURITY DEFINER
AS $$
  SELECT EXISTS (
    SELECT 1
    FROM public.user_roles
    WHERE user_id = auth.uid()
      AND role IN ('admin', 'moderator')
  );
$$;

-- Harden admin audit access
ALTER TABLE public.admin_audit_log ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS enforce_admin_moderator_access ON public.admin_audit_log;
CREATE POLICY enforce_admin_moderator_access ON public.admin_audit_log
  FOR ALL
  TO authenticated
  USING (public.is_admin_or_moderator())
  WITH CHECK (public.is_admin_or_moderator());

-- Harden site settings admin write access
ALTER TABLE public.site_settings ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS site_settings_admin_write ON public.site_settings;
CREATE POLICY site_settings_admin_write ON public.site_settings
  FOR ALL
  TO authenticated
  USING (public.is_admin_or_moderator())
  WITH CHECK (public.is_admin_or_moderator());

-- Harden support ticket staff updates
ALTER TABLE public.support_tickets ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS tix_staff_update ON public.support_tickets;
CREATE POLICY enforce_support_staff_access ON public.support_tickets
  FOR ALL
  TO authenticated
  USING (
    user_id = auth.uid()
    OR public.is_admin_or_moderator()
  )
  WITH CHECK (
    user_id = auth.uid()
    OR public.is_admin_or_moderator()
  );
