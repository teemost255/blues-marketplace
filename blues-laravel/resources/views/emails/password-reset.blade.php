<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset your password</title>
<style>
  body { margin: 0; padding: 0; background: #0f172a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
  .wrapper { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
  .card { background: #1e293b; border-radius: 16px; overflow: hidden; }
  .header { background: linear-gradient(135deg, #0284c7, #0369a1); padding: 36px 40px; text-align: center; }
  .header-icon { width: 52px; height: 52px; background: rgba(255,255,255,0.15); border-radius: 14px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; }
  .header h1 { color: #fff; font-size: 22px; font-weight: 700; margin: 0; }
  .header p { color: rgba(255,255,255,0.75); font-size: 14px; margin: 6px 0 0; }
  .body { padding: 36px 40px; }
  .body p { color: #cbd5e1; font-size: 15px; line-height: 1.6; margin: 0 0 20px; }
  .btn-wrap { text-align: center; margin: 28px 0; }
  .btn { display: inline-block; background: #0284c7; color: #fff; text-decoration: none; font-size: 15px; font-weight: 600; padding: 14px 36px; border-radius: 10px; }
  .link-fallback { background: #0f172a; border-radius: 8px; padding: 14px 16px; margin: 20px 0 0; }
  .link-fallback p { color: #64748b; font-size: 12px; margin: 0 0 6px; }
  .link-fallback a { color: #38bdf8; font-size: 12px; word-break: break-all; }
  .footer { padding: 20px 40px 32px; text-align: center; }
  .footer p { color: #475569; font-size: 12px; margin: 0; line-height: 1.6; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div class="header-icon">
        <svg width="26" height="26" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
      </div>
      <h1>Reset your password</h1>
      <p>A request was made to reset your {{ $siteName }} password</p>
    </div>
    <div class="body">
      <p>Hi {{ $user->name }},</p>
      <p>We received a request to reset the password for your account. Click the button below to choose a new password. This link will expire in <strong style="color:#f1f5f9">60 minutes</strong>.</p>
      <div class="btn-wrap">
        <a href="{{ $resetUrl }}" class="btn">Reset My Password</a>
      </div>
      <p>If you didn't request a password reset, you can safely ignore this email — your password will not change.</p>
      <div class="link-fallback">
        <p>Button not working? Copy and paste this link into your browser:</p>
        <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
      </div>
    </div>
    <div class="footer">
      <p>© {{ date('Y') }} {{ $siteName }}. You're receiving this because a password reset was requested for your account.</p>
    </div>
  </div>
</div>
</body>
</html>
