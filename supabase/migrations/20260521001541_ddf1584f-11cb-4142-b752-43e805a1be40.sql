
REVOKE EXECUTE ON FUNCTION public.wallet_checkout(uuid) FROM anon, public;
REVOKE EXECUTE ON FUNCTION public.wallet_deposit_mock(numeric) FROM anon, public;
GRANT EXECUTE ON FUNCTION public.wallet_checkout(uuid) TO authenticated;
GRANT EXECUTE ON FUNCTION public.wallet_deposit_mock(numeric) TO authenticated;

DROP POLICY IF EXISTS avatars_public_read ON storage.objects;
CREATE POLICY avatars_owner_or_referenced_read ON storage.objects FOR SELECT
  USING (bucket_id = 'avatars' AND (auth.uid()::text = (storage.foldername(name))[1] OR auth.role() = 'anon'));
