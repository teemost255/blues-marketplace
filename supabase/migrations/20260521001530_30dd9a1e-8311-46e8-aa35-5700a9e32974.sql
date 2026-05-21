
-- WALLETS
CREATE TABLE public.wallets (
  user_id uuid PRIMARY KEY,
  balance numeric NOT NULL DEFAULT 0 CHECK (balance >= 0),
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);
ALTER TABLE public.wallets ENABLE ROW LEVEL SECURITY;
CREATE POLICY wallets_select_own_or_admin ON public.wallets FOR SELECT
  USING (user_id = auth.uid() OR public.has_role(auth.uid(),'admin'));
CREATE POLICY wallets_admin_all ON public.wallets FOR ALL
  USING (public.has_role(auth.uid(),'admin')) WITH CHECK (public.has_role(auth.uid(),'admin'));
CREATE TRIGGER wallets_set_updated BEFORE UPDATE ON public.wallets
  FOR EACH ROW EXECUTE FUNCTION public.tg_set_updated_at();

-- WALLET TRANSACTIONS
CREATE TABLE public.wallet_transactions (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL,
  amount numeric NOT NULL,
  type text NOT NULL CHECK (type IN ('deposit','purchase','refund','bonus')),
  status text NOT NULL DEFAULT 'completed' CHECK (status IN ('pending','completed','failed')),
  reference text,
  description text,
  created_at timestamptz NOT NULL DEFAULT now()
);
ALTER TABLE public.wallet_transactions ENABLE ROW LEVEL SECURITY;
CREATE POLICY wtx_select_own_or_admin ON public.wallet_transactions FOR SELECT
  USING (user_id = auth.uid() OR public.has_role(auth.uid(),'admin'));
CREATE POLICY wtx_insert_own ON public.wallet_transactions FOR INSERT
  WITH CHECK (user_id = auth.uid());
CREATE POLICY wtx_admin_all ON public.wallet_transactions FOR ALL
  USING (public.has_role(auth.uid(),'admin')) WITH CHECK (public.has_role(auth.uid(),'admin'));
CREATE INDEX idx_wtx_user_created ON public.wallet_transactions(user_id, created_at DESC);

-- Auto-create wallet on profile signup
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS trigger LANGUAGE plpgsql SECURITY DEFINER SET search_path TO 'public' AS $$
begin
  insert into public.profiles (id, display_name, avatar_url, username, phone, country, referral_code)
  values (
    new.id,
    coalesce(new.raw_user_meta_data->>'display_name', new.raw_user_meta_data->>'name', split_part(new.email, '@', 1)),
    new.raw_user_meta_data->>'avatar_url',
    new.raw_user_meta_data->>'username',
    new.raw_user_meta_data->>'phone',
    new.raw_user_meta_data->>'country',
    new.raw_user_meta_data->>'referral_code'
  ) on conflict (id) do nothing;
  insert into public.user_roles (user_id, role) values (new.id, 'user') on conflict do nothing;
  insert into public.wallets (user_id, balance) values (new.id, 0) on conflict do nothing;
  return new;
end; $$;

-- Backfill wallets
INSERT INTO public.wallets (user_id) SELECT id FROM public.profiles ON CONFLICT DO NOTHING;

-- WISHLIST
CREATE TABLE public.wishlists (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL,
  listing_id uuid NOT NULL,
  created_at timestamptz NOT NULL DEFAULT now(),
  UNIQUE(user_id, listing_id)
);
ALTER TABLE public.wishlists ENABLE ROW LEVEL SECURITY;
CREATE POLICY wishlists_own ON public.wishlists FOR ALL
  USING (user_id = auth.uid()) WITH CHECK (user_id = auth.uid());

-- NOTIFICATIONS
CREATE TABLE public.notifications (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL,
  title text NOT NULL,
  body text,
  type text NOT NULL DEFAULT 'info',
  read boolean NOT NULL DEFAULT false,
  link text,
  created_at timestamptz NOT NULL DEFAULT now()
);
ALTER TABLE public.notifications ENABLE ROW LEVEL SECURITY;
CREATE POLICY notif_select_own ON public.notifications FOR SELECT USING (user_id = auth.uid());
CREATE POLICY notif_update_own ON public.notifications FOR UPDATE USING (user_id = auth.uid());
CREATE POLICY notif_staff_insert ON public.notifications FOR INSERT
  WITH CHECK (public.has_role(auth.uid(),'admin') OR public.has_role(auth.uid(),'moderator') OR user_id = auth.uid());
CREATE INDEX idx_notif_user_created ON public.notifications(user_id, created_at DESC);

-- SUPPORT TICKETS
CREATE TABLE public.support_tickets (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL,
  subject text NOT NULL,
  message text NOT NULL,
  status text NOT NULL DEFAULT 'open' CHECK (status IN ('open','in_progress','resolved','closed')),
  priority text NOT NULL DEFAULT 'normal' CHECK (priority IN ('low','normal','high','urgent')),
  admin_reply text,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);
ALTER TABLE public.support_tickets ENABLE ROW LEVEL SECURITY;
CREATE POLICY tix_own_select ON public.support_tickets FOR SELECT
  USING (user_id = auth.uid() OR public.has_role(auth.uid(),'admin') OR public.has_role(auth.uid(),'moderator'));
CREATE POLICY tix_own_insert ON public.support_tickets FOR INSERT WITH CHECK (user_id = auth.uid());
CREATE POLICY tix_staff_update ON public.support_tickets FOR UPDATE
  USING (public.has_role(auth.uid(),'admin') OR public.has_role(auth.uid(),'moderator'));
CREATE TRIGGER tix_set_updated BEFORE UPDATE ON public.support_tickets
  FOR EACH ROW EXECUTE FUNCTION public.tg_set_updated_at();

-- ACTIVITY LOG
CREATE TABLE public.activity_log (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL,
  action text NOT NULL,
  meta jsonb NOT NULL DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now()
);
ALTER TABLE public.activity_log ENABLE ROW LEVEL SECURITY;
CREATE POLICY act_own_select ON public.activity_log FOR SELECT
  USING (user_id = auth.uid() OR public.has_role(auth.uid(),'admin'));
CREATE POLICY act_own_insert ON public.activity_log FOR INSERT WITH CHECK (user_id = auth.uid());
CREATE INDEX idx_act_user ON public.activity_log(user_id, created_at DESC);

-- STORAGE bucket for avatars
INSERT INTO storage.buckets (id, name, public) VALUES ('avatars','avatars', true)
ON CONFLICT (id) DO NOTHING;
CREATE POLICY avatars_public_read ON storage.objects FOR SELECT USING (bucket_id = 'avatars');
CREATE POLICY avatars_user_write ON storage.objects FOR INSERT
  WITH CHECK (bucket_id = 'avatars' AND auth.uid()::text = (storage.foldername(name))[1]);
CREATE POLICY avatars_user_update ON storage.objects FOR UPDATE
  USING (bucket_id = 'avatars' AND auth.uid()::text = (storage.foldername(name))[1]);
CREATE POLICY avatars_user_delete ON storage.objects FOR DELETE
  USING (bucket_id = 'avatars' AND auth.uid()::text = (storage.foldername(name))[1]);

-- SECURE WALLET RPC: atomic checkout
CREATE OR REPLACE FUNCTION public.wallet_checkout(_listing_id uuid)
RETURNS jsonb LANGUAGE plpgsql SECURITY DEFINER SET search_path TO 'public' AS $$
DECLARE
  _uid uuid := auth.uid();
  _price numeric;
  _stock int;
  _title text;
  _balance numeric;
  _purchase_id uuid;
BEGIN
  IF _uid IS NULL THEN RAISE EXCEPTION 'Not authenticated'; END IF;
  SELECT price, stock, title INTO _price, _stock, _title FROM public.listings
    WHERE id = _listing_id AND is_active = true FOR UPDATE;
  IF _price IS NULL THEN RAISE EXCEPTION 'Listing not found'; END IF;
  IF _stock < 1 THEN RAISE EXCEPTION 'Out of stock'; END IF;
  SELECT balance INTO _balance FROM public.wallets WHERE user_id = _uid FOR UPDATE;
  IF _balance IS NULL THEN
    INSERT INTO public.wallets(user_id,balance) VALUES(_uid,0);
    _balance := 0;
  END IF;
  IF _balance < _price THEN RAISE EXCEPTION 'Insufficient wallet balance'; END IF;

  UPDATE public.wallets SET balance = balance - _price WHERE user_id = _uid;
  UPDATE public.listings SET stock = stock - 1 WHERE id = _listing_id;
  INSERT INTO public.purchases(user_id, listing_id, amount, status)
    VALUES(_uid, _listing_id, _price, 'completed') RETURNING id INTO _purchase_id;
  INSERT INTO public.wallet_transactions(user_id, amount, type, status, description)
    VALUES(_uid, -_price, 'purchase', 'completed', 'Purchase: ' || _title);
  INSERT INTO public.notifications(user_id, title, body, type, link)
    VALUES(_uid, 'Purchase complete', 'Your order for ' || _title || ' is ready.', 'success', '/dashboard/orders');
  RETURN jsonb_build_object('purchase_id', _purchase_id, 'new_balance', _balance - _price);
END; $$;

-- MOCK PAYSTACK DEPOSIT RPC
CREATE OR REPLACE FUNCTION public.wallet_deposit_mock(_amount numeric)
RETURNS jsonb LANGUAGE plpgsql SECURITY DEFINER SET search_path TO 'public' AS $$
DECLARE _uid uuid := auth.uid(); _new numeric;
BEGIN
  IF _uid IS NULL THEN RAISE EXCEPTION 'Not authenticated'; END IF;
  IF _amount <= 0 OR _amount > 1000000 THEN RAISE EXCEPTION 'Invalid amount'; END IF;
  INSERT INTO public.wallets(user_id, balance) VALUES(_uid, _amount)
    ON CONFLICT (user_id) DO UPDATE SET balance = public.wallets.balance + _amount
    RETURNING balance INTO _new;
  INSERT INTO public.wallet_transactions(user_id, amount, type, status, reference, description)
    VALUES(_uid, _amount, 'deposit', 'completed', 'MOCK-' || gen_random_uuid()::text, 'Test deposit (mock Paystack)');
  INSERT INTO public.notifications(user_id, title, body, type)
    VALUES(_uid, 'Wallet topped up', '₦' || _amount || ' added to your wallet.', 'success');
  RETURN jsonb_build_object('new_balance', _new);
END; $$;
