@extends('layouts.app')
@section('title', 'Terms of Service')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-3xl font-bold text-white mb-2">Terms of Service</h1>
    <p class="text-slate-400 text-sm mb-10">Last updated: {{ date('F j, Y') }}</p>

    <div class="prose prose-invert max-w-none space-y-8 text-slate-300 text-sm leading-relaxed">

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">1. Acceptance of Terms</h2>
            <p>By accessing and using BluesMarketplace ("the Platform"), you agree to be bound by these Terms of Service. If you do not agree, please do not use the Platform.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">2. Description of Service</h2>
            <p>BluesMarketplace is a digital marketplace that facilitates the buying and selling of digital accounts including Facebook, Instagram, TikTok accounts, and secondary phone numbers. We act solely as an intermediary and are not responsible for the content of any listing.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">3. User Accounts</h2>
            <p>You must be at least 18 years old to use the Platform. You are responsible for maintaining the security of your account credentials. You must provide accurate and truthful information during registration. We reserve the right to suspend or terminate accounts that violate these terms.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">4. Purchases and Payments</h2>
            <p>All purchases are made using wallet funds loaded onto the Platform. Prices are displayed in USD. All sales are final unless a dispute is raised and found to be valid. We do not guarantee the ongoing availability of purchased accounts on third-party platforms.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">5. Prohibited Conduct</h2>
            <p>You agree not to: use the Platform for any illegal purpose; attempt to circumvent platform security; create multiple accounts to abuse referral or promotional systems; use purchased accounts for spam, fraud, or any activity that violates third-party platform terms.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">6. Disclaimer of Warranties</h2>
            <p>The Platform is provided "as is" without warranties of any kind. We do not warrant that the service will be uninterrupted, error-free, or that purchased accounts will remain accessible indefinitely on their respective platforms.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">7. Limitation of Liability</h2>
            <p>BluesMarketplace shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the Platform or purchase of any digital accounts.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">8. Changes to Terms</h2>
            <p>We reserve the right to modify these terms at any time. Continued use of the Platform after changes constitutes acceptance of the updated terms.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">9. Contact</h2>
            <p>If you have questions about these terms, please <a href="{{ route('dashboard.support') }}" class="text-brand hover:underline">contact our support team</a>.</p>
        </section>
    </div>
</div>
@endsection
