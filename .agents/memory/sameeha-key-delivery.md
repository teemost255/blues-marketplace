---
name: Sameeha key delivery
description: Provider contract for delivering catalog product keys to marketplace buyers.
---

Sameeha Social Hub returns purchased keys in the `POST /buy` response. Its supplied documentation does not describe an order-history or key-retrieval endpoint. Credential values may arrive as structured records, not only as plain strings.

**Why:** A supplier can debit the account even when the app cannot parse or receive the returned credentials. Treating that as an ordinary failure can refund the buyer while losing the supplier charge and the one-time keys.

**How to apply:** Persist a pending purchase attempt before calling the provider. Normalize structured credential records, then save returned keys locally. Keep incomplete 2xx responses, timeouts, and server errors pending without an automatic refund or retry until the supplier outcome is confirmed. Do not rely on a provider history endpoint unless updated API documentation confirms one.
