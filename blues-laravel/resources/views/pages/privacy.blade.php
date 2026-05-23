@extends('layouts.app')
@section('title', 'Privacy Policy')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-3xl font-bold text-white mb-2">Privacy Policy</h1>
    <p class="text-slate-400 text-sm mb-10">Last updated: {{ date('F j, Y') }}</p>

    <div class="prose prose-invert max-w-none space-y-8 text-slate-300 text-sm leading-relaxed">

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">1. Information We Collect</h2>
            <p>We collect information you provide directly: your name, email address, and password when registering. We also collect transactional data such as purchases, wallet activity, and support interactions. We do not collect payment card data.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">2. How We Use Your Information</h2>
            <p>We use your information to: provide and improve our services; process transactions; send account notifications; respond to support requests; and detect and prevent fraud.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">3. Data Storage and Security</h2>
            <p>Your data is stored securely on our servers. Passwords are hashed and never stored in plain text. We implement industry-standard security measures to protect against unauthorized access, alteration, or disclosure.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">4. Data Sharing</h2>
            <p>We do not sell, trade, or rent your personal information to third parties. We may share data with service providers who assist in operating the Platform, subject to confidentiality agreements.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">5. Cookies</h2>
            <p>We use session cookies to keep you logged in. We do not use tracking cookies for advertising purposes.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">6. Your Rights</h2>
            <p>You have the right to access, correct, or delete your personal data. To exercise these rights, contact us through the support system. Account deletion will remove all your personal data within 30 days.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">7. Changes to This Policy</h2>
            <p>We may update this policy periodically. Significant changes will be notified via your account notifications.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-white mb-3">8. Contact</h2>
            <p>Privacy-related inquiries can be submitted via our <a href="{{ route('dashboard.support') }}" class="text-brand hover:underline">support system</a>.</p>
        </section>
    </div>
</div>
@endsection
