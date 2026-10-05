---
name: Sameeha key delivery
description: Provider contract for delivering catalog product keys to marketplace buyers.
---

Sameeha Social Hub returns purchased keys in the successful `POST /buy` response. Its supplied documentation does not describe an order-history or key-retrieval endpoint.

**Why:** The provider says keys are returned once in the purchase response, so losing that response or failing to save its keys can leave an order without recoverable credentials.

**How to apply:** Persist the returned keys with the app's purchase record immediately after a successful buy. Do not rely on a provider history endpoint unless updated API documentation confirms one.
