
REVOKE EXECUTE ON FUNCTION public.handle_new_user() FROM PUBLIC, anon, authenticated;
REVOKE EXECUTE ON FUNCTION public.tg_set_updated_at() FROM PUBLIC, anon, authenticated;
REVOKE EXECUTE ON FUNCTION public.validate_listing_category() FROM PUBLIC, anon, authenticated;
REVOKE EXECUTE ON FUNCTION public.is_admin_or_moderator() FROM PUBLIC, anon;
REVOKE EXECUTE ON FUNCTION public.wallet_checkout(uuid) FROM PUBLIC, anon;
REVOKE EXECUTE ON FUNCTION public.wallet_deposit_mock(numeric) FROM PUBLIC, anon;
