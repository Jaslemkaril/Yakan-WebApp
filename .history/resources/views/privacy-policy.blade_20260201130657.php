@extends('layouts.app')

@section('content')
<div class="container py-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-8 text-red-800">Privacy Policy</h1>
        
        <div class="prose prose-lg">
            <h2 class="text-2xl font-bold mt-8 mb-4">1. Information We Collect</h2>
            <p>
                When you use Yakan's services, we collect the following types of information:
            </p>
            <ul class="list-disc list-inside">
                <li><strong>Account Information:</strong> Name, email address, password, phone number</li>
                <li><strong>Order Information:</strong> Shipping address, billing address, items ordered, payment details</li>
                <li><strong>Authentication Data:</strong> Social media profiles (Google, Facebook) for login purposes</li>
                <li><strong>Usage Data:</strong> Pages visited, products viewed, search queries, device information</li>
                <li><strong>Communication:</strong> Messages, support tickets, feedback</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4">2. How We Use Your Information</h2>
            <p>
                We use your information to:
            </p>
            <ul class="list-disc list-inside">
                <li>Process and fulfill your orders</li>
                <li>Send order confirmations and delivery updates</li>
                <li>Provide customer support</li>
                <li>Prevent fraud and secure your account</li>
                <li>Improve our products and services</li>
                <li>Comply with legal and regulatory requirements</li>
                <li>Send promotional content (only with your consent)</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4">3. Data Security</h2>
            <p>
                We implement industry-standard security measures including:
            </p>
            <ul class="list-disc list-inside">
                <li>SSL/TLS encryption for data transmission</li>
                <li>Secure password hashing and storage</li>
                <li>Regular security audits</li>
                <li>Access controls and authentication</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4">4. Your Rights</h2>
            <p>
                You have the right to:
            </p>
            <ul class="list-disc list-inside">
                <li>Access your personal data</li>
                <li>Request correction of inaccurate information</li>
                <li>Request deletion of your account and data</li>
                <li>Withdraw consent for data processing</li>
                <li>Opt-out of promotional communications</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4">5. Third-Party Services</h2>
            <p>
                We integrate with:
            </p>
            <ul class="list-disc list-inside">
                <li><strong>Google OAuth:</strong> For social login</li>
                <li><strong>Facebook OAuth:</strong> For social login</li>
                <li><strong>Payment Providers:</strong> GCash, Online Banking for secure transactions</li>
                <li><strong>Email Services:</strong> Gmail for notifications and support</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4">6. Data Retention</h2>
            <p>
                We retain your data for as long as your account is active. After account deletion, we retain anonymized data for legal compliance and fraud prevention purposes for up to 7 years.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">7. Contact Us</h2>
            <p>
                If you have privacy concerns or questions, please contact us at:
            </p>
            <p class="font-semibold">
                Email: <a href="mailto:eh202202743@wmsu.edu.ph" class="text-red-600 hover:underline">eh202202743@wmsu.edu.ph</a>
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">8. Policy Updates</h2>
            <p>
                We may update this privacy policy from time to time. We will notify you of significant changes via email or through the application.
            </p>

            <p class="text-gray-600 text-sm mt-12 border-t pt-4">
                <strong>Last updated:</strong> {{ now()->format('F d, Y') }}<br>
                <strong>Yakan E-commerce Platform</strong>
            </p>
        </div>
    </div>
</div>
@endsection
