<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Purchase Confirmed</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#0f172a;color:#e2e8f0;line-height:1.6}
  .wrap{max-width:600px;margin:0 auto;padding:24px 16px}
  .card{background:#1e293b;border-radius:16px;overflow:hidden;border:1px solid #334155}
  .header{background:linear-gradient(135deg,#0ea5e9,#6366f1);padding:40px 32px;text-align:center}
  .header h1{color:#fff;font-size:24px;font-weight:800;margin-bottom:4px;letter-spacing:-0.5px}
  .header p{color:rgba(255,255,255,0.8);font-size:14px}
  .check-circle{width:64px;height:64px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:32px}
  .body{padding:32px}
  .greeting{font-size:18px;font-weight:700;color:#f1f5f9;margin-bottom:8px}
  .subtext{color:#94a3b8;font-size:14px;margin-bottom:28px}
  .order-box{background:#0f172a;border-radius:12px;border:1px solid #334155;padding:20px;margin-bottom:24px}
  .order-box .label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#64748b;margin-bottom:12px}
  .row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #1e293b}
  .row:last-child{border-bottom:none}
  .row .key{color:#94a3b8;font-size:13px}
  .row .val{color:#f1f5f9;font-size:13px;font-weight:600}
  .badge{display:inline-block;background:#16a34a;color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:999px}
  .cta-btn{display:block;background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;text-decoration:none;font-weight:700;font-size:15px;text-align:center;padding:14px 28px;border-radius:10px;margin:24px 0}
  .details-box{background:#0f172a;border:1px solid #4ade80;border-radius:12px;padding:20px;margin-bottom:24px}
  .details-box .dlabel{font-size:12px;font-weight:700;color:#4ade80;text-transform:uppercase;letter-spacing:1px;margin-bottom:10px}
  .details-box p{color:#86efac;font-size:13px;margin-top:6px}
  .footer{padding:20px 32px;text-align:center;border-top:1px solid #334155}
  .footer p{color:#475569;font-size:12px;line-height:1.7}
  .footer a{color:#0ea5e9;text-decoration:none}
  .site-name{color:#0ea5e9;font-weight:700}
</style>
</head>
<body>
<div class="wrap">
  <div style="text-align:center;margin-bottom:20px">
    <span style="font-size:20px;font-weight:800;color:#0ea5e9">⚡ <span class="site-name">{{ $siteName }}</span></span>
  </div>
  <div class="card">
    <div class="header">
      <div class="check-circle">✓</div>
      <h1>Purchase Confirmed!</h1>
      <p>Your order has been processed successfully.</p>
    </div>
    <div class="body">
      <p class="greeting">Hi, {{ $user->name }}!</p>
      <p class="subtext">Your purchase is complete. Here's a summary of what you just bought.</p>

      <div class="order-box">
        <div class="label">Order Details</div>
        <div class="row"><span class="key">Item</span><span class="val">{{ $listing->title }}</span></div>
        <div class="row"><span class="key">Amount Paid</span><span class="val">₦{{ number_format($purchase->amount, 2) }}</span></div>
        <div class="row"><span class="key">Order ID</span><span class="val">#{{ $purchase->id }}</span></div>
        <div class="row"><span class="key">Status</span><span class="val"><span class="badge">Completed</span></span></div>
        <div class="row"><span class="key">Date</span><span class="val">{{ $purchase->created_at->format('M d, Y H:i') }}</span></div>
      </div>

      @if($hasDetails)
      <div class="details-box">
        <div class="dlabel">🎉 Your Account Credentials Are Ready</div>
        <p>Log in to your dashboard and go to <strong>My Orders</strong> to view and copy your login details.</p>
      </div>
      @else
      <div class="details-box">
        <div class="dlabel">Delivery in Progress</div>
        <p>Your order is being processed. Check <strong>My Orders</strong> in your dashboard for delivery updates.</p>
      </div>
      @endif

      <a href="{{ config('app.url') }}/dashboard/orders" class="cta-btn">View My Orders →</a>

      <p style="font-size:13px;color:#64748b;text-align:center">
        Need help? <a href="{{ config('app.url') }}/dashboard/support" style="color:#0ea5e9">Open a support ticket</a> and our team will assist you.
      </p>
    </div>
    <div class="footer">
      <p>You're receiving this email because your account at <a href="{{ config('app.url') }}">{{ $siteName }}</a> made a purchase.<br>
      You can manage your email preferences in your <a href="{{ config('app.url') }}/dashboard/profile">account settings</a>.</p>
    </div>
  </div>
  <p style="text-align:center;color:#334155;font-size:11px;margin-top:16px">© {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
</div>
</body>
</html>
