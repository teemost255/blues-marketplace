<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $title }}</title>
<style>
  body { margin: 0; padding: 0; background: #0f172a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
  .wrapper { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
  .card { background: #1e293b; border-radius: 16px; overflow: hidden; }
  .header { padding: 36px 40px 28px; text-align: center; }
  .header h1 { color: #f1f5f9; font-size: 20px; font-weight: 700; margin: 0 0 4px; }
  .header .brand { color: #38bdf8; font-size: 13px; font-weight: 600; margin: 0 0 20px; }
  .badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; margin-bottom: 16px; }
  .badge-info    { background: #0284c720; color: #38bdf8; }
  .badge-success { background: #16a34a20; color: #4ade80; }
  .badge-warning { background: #ca8a0420; color: #fbbf24; }
  .badge-error   { background: #dc262620; color: #f87171; }
  .divider { height: 1px; background: #334155; margin: 0 40px; }
  .body { padding: 28px 40px 36px; }
  .body p { color: #cbd5e1; font-size: 15px; line-height: 1.7; margin: 0 0 16px; white-space: pre-wrap; }
  .footer { padding: 20px 40px 32px; text-align: center; }
  .footer p { color: #475569; font-size: 12px; margin: 0; line-height: 1.6; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <p class="brand">{{ $siteName }}</p>
      @php
        $badgeClass = 'badge-' . $type;
        $badgeLabel = ['info' => 'Info', 'success' => 'Good News', 'warning' => 'Heads Up', 'error' => 'Alert'][$type] ?? 'Notice';
      @endphp
      <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
      <h1>{{ $title }}</h1>
    </div>
    <div class="divider"></div>
    <div class="body">
      <p>Hi {{ $user->name }},</p>
      <p>{{ $message }}</p>
    </div>
    <div class="footer">
      <p>© {{ date('Y') }} {{ $siteName }}. You received this because you have an account on our platform.</p>
    </div>
  </div>
</div>
</body>
</html>
