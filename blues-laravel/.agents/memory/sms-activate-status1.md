---
name: SMS-Activate setStatus=1 side effect
description: Calling setStatus=1 after ordering a HeroSMS number resets the activation state, causing OTP to show on provider dashboard but not on the platform.
---

## Rule
Never call `setStatus=1` immediately after `getNumber` in the HeroSMS order flow.

**Why:** In the SMS-Activate protocol, `setStatus=1` means "I need another SMS" (multi-SMS activation). If called after an OTP has already been delivered, it signals to the provider "mark current code as consumed, send me a new one." The provider records STATUS_OK on their dashboard but `getStatus` then returns STATUS_WAIT_CODE again — so the platform keeps seeing "waiting" forever even though the code arrived.

**How to apply:** The `readyForSms()` method exists in HeroSmsService but must NOT be called from `orderHeroSms`. Remove any call site that does this. If re-evaluating in future, test on a live order and watch laravel.log for the getStatus response before and after.
