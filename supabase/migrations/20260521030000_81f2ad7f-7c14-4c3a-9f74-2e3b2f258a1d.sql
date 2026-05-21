-- Add moderator role support and update admin/moderator RLS policies
ALTER TYPE public.app_role ADD VALUE IF NOT EXISTS 'moderator';

-- Admin/Moderator audit read access
DROP POLICY IF EXISTS admin_audit_admin_read ON public.admin_audit_log;
CREATE POLICY admin_audit_admin_read ON public.admin_audit_log
  FOR SELECT USING (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

-- Profiles: allow admins and moderators the same broad access as existing admin rules
DROP POLICY IF EXISTS profiles_select_own_or_admin ON public.profiles;
CREATE POLICY profiles_select_own_or_admin ON public.profiles
  FOR SELECT USING (
    auth.uid() = id
    OR public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

DROP POLICY IF EXISTS profiles_admin_update ON public.profiles;
CREATE POLICY profiles_admin_update ON public.profiles
  FOR UPDATE USING (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

-- User roles: allow admins and moderators the same administrative access
DROP POLICY IF EXISTS user_roles_select_own_or_admin ON public.user_roles;
CREATE POLICY user_roles_select_own_or_admin ON public.user_roles
  FOR SELECT USING (
    user_id = auth.uid()
    OR public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

DROP POLICY IF EXISTS user_roles_admin_all ON public.user_roles;
CREATE POLICY user_roles_admin_all ON public.user_roles
  FOR ALL USING (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  ) WITH CHECK (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

-- Listings admin operations can be performed by admins or moderators
DROP POLICY IF EXISTS listings_admin_insert ON public.listings;
CREATE POLICY listings_admin_insert ON public.listings
  FOR INSERT WITH CHECK (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

DROP POLICY IF EXISTS listings_admin_update ON public.listings;
CREATE POLICY listings_admin_update ON public.listings
  FOR UPDATE USING (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

DROP POLICY IF EXISTS listings_admin_delete ON public.listings;
CREATE POLICY listings_admin_delete ON public.listings
  FOR DELETE USING (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

-- Purchases: admin or moderator can read and update administrative purchase data
DROP POLICY IF EXISTS purchases_select_own_or_admin ON public.purchases;
CREATE POLICY purchases_select_own_or_admin ON public.purchases
  FOR SELECT USING (
    user_id = auth.uid()
    OR public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

DROP POLICY IF EXISTS purchases_admin_update ON public.purchases;
CREATE POLICY purchases_admin_update ON public.purchases
  FOR UPDATE USING (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

-- Wallets: admins and moderators should also be able to access and manage wallet admin operations
DROP POLICY IF EXISTS wallets_select_own_or_admin ON public.wallets;
CREATE POLICY wallets_select_own_or_admin ON public.wallets
  FOR SELECT USING (
    user_id = auth.uid()
    OR public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

DROP POLICY IF EXISTS wallets_admin_all ON public.wallets;
CREATE POLICY wallets_admin_all ON public.wallets
  FOR ALL USING (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  ) WITH CHECK (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

-- Wallet transactions: admin or moderator can access all transaction data
DROP POLICY IF EXISTS wtx_select_own_or_admin ON public.wallet_transactions;
CREATE POLICY wtx_select_own_or_admin ON public.wallet_transactions
  FOR SELECT USING (
    user_id = auth.uid()
    OR public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

DROP POLICY IF EXISTS wtx_admin_all ON public.wallet_transactions;
CREATE POLICY wtx_admin_all ON public.wallet_transactions
  FOR ALL USING (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  ) WITH CHECK (
    public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );

-- Activity log: admins and moderators can select administrative activity records
DROP POLICY IF EXISTS act_own_select ON public.activity_log;
CREATE POLICY act_own_select ON public.activity_log
  FOR SELECT USING (
    user_id = auth.uid()
    OR public.has_role(auth.uid(), 'admin'::app_role)
    OR public.has_role(auth.uid(), 'moderator'::app_role)
  );
