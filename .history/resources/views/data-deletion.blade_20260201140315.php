@extends('layouts.app')

@section('content')
<div class="container py-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-8 text-red-800">Data Deletion</h1>
        
        <div class="prose prose-lg">
            <h2 class="text-2xl font-bold mt-8 mb-4">Delete Your Account and Data</h2>
            
            @if(Auth::check())
                <h3 class="text-xl font-bold mt-6 mb-3">Permanent Account Deletion</h3>
                <div class="bg-red-50 p-6 rounded border-2 border-red-200 mb-6">
                    <p class="text-red-800 font-semibold mb-4">
                        ⚠️ Warning: This action is PERMANENT and cannot be undone.
                    </p>
                    <p class="mb-4">
                        Deleting your account will immediately remove all your personal data from our system.
                    </p>
                    <form id="deleteAccountForm" method="POST" action="{{ route('account.delete') }}" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label for="password" class="block text-sm font-semibold mb-2">
                                Enter your password to confirm deletion:
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="Your password"
                                required
                            >
                            @error('password')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="confirm" 
                                name="confirm" 
                                class="w-4 h-4 text-red-600"
                                required
                            >
                            <label for="confirm" class="ml-2 text-sm">
                                I understand this is permanent and cannot be reversed
                            </label>
                        </div>

                        <button 
                            type="button"
                            onclick="confirmDelete()"
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded w-full"
                        >
                            Delete My Account and Data
                        </button>
                    </form>
                </div>
            @else
                <h3 class="text-xl font-bold mt-6 mb-3">Self-Service Account Deletion</h3>
                <div class="bg-blue-50 p-6 rounded mb-6">
                    <p class="mb-4">
                        You must be <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-semibold">logged in</a> to delete your account.
                    </p>
                </div>
            @endif
            
            <h3 class="text-xl font-bold mt-8 mb-3">Request via Email</h3>
            <p>
                If you cannot access your account, send an email to:
            </p>
            <p class="font-semibold text-lg bg-gray-100 p-4 rounded">
                <a href="mailto:eh202202743@wmsu.edu.ph" class="text-red-600 hover:underline">eh202202743@wmsu.edu.ph</a>
            </p>
            <p>
                Include the following in your email:
            </p>
            <ul class="list-disc list-inside">
                <li>Your full name</li>
                <li>Email address associated with your account</li>
                <li>Subject line: "Data Deletion Request"</li>
                <li>(Optional) Reason for deletion</li>
            </ul>
            <p class="text-red-600 font-semibold">
                We will process your request within 30 days and send confirmation via email.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">What Gets Deleted</h2>
            <p class="font-semibold text-lg bg-red-50 p-4 rounded mb-4">
                ✓ All the following data will be permanently removed:
            </p>
            <ul class="list-disc list-inside space-y-2">
                <li>Personal profile information (name, email, phone)</li>
                <li>Account credentials and password</li>
                <li>Order history and receipts</li>
                <li>Payment and billing information</li>
                <li>Wishlist and saved items</li>
                <li>Address book and shipping addresses</li>
                <li>Session data and cookies</li>
                <li>Communication history with customer support</li>
                <li>Social media connections (Google, Facebook OAuth links)</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4">What We Keep (For Legal Reasons)</h2>
            <p class="font-semibold text-lg bg-blue-50 p-4 rounded mb-4">
                ⚠ These records will be retained in anonymized form:
            </p>
            <ul class="list-disc list-inside space-y-2">
                <li><strong>Transaction Records:</strong> Anonymized transaction history for accounting purposes</li>
                <li><strong>Tax Records:</strong> Invoices and tax compliance data (required by law)</li>
                <li><strong>Fraud Prevention:</strong> Anonymized data to prevent fraudulent activities</li>
                <li><strong>Legal Compliance:</strong> Records required by government regulations</li>
            </ul>
            <p class="text-sm text-gray-600">
                Note: These records no longer contain identifying information and cannot be linked back to you.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">Data Deletion Process Timeline</h2>
            <table class="w-full border-collapse border border-gray-300">
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 p-2 text-left">Step</th>
                    <th class="border border-gray-300 p-2 text-left">Timeline</th>
                    <th class="border border-gray-300 p-2 text-left">What Happens</th>
                </tr>
                <tr>
                    <td class="border border-gray-300 p-2">1. Request Submitted</td>
                    <td class="border border-gray-300 p-2">Immediately</td>
                    <td class="border border-gray-300 p-2">You request deletion</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 p-2">2. Processing</td>
                    <td class="border border-gray-300 p-2">1-7 days</td>
                    <td class="border border-gray-300 p-2">We verify your request and prepare deletion</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 p-2">3. Deletion</td>
                    <td class="border border-gray-300 p-2">7-30 days</td>
                    <td class="border border-gray-300 p-2">Your data is permanently deleted from systems</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 p-2">4. Confirmation</td>
                    <td class="border border-gray-300 p-2">Within 30 days</td>
                    <td class="border border-gray-300 p-2">You receive email confirmation</td>
                </tr>
            </table>

            <h2 class="text-2xl font-bold mt-8 mb-4">Frequently Asked Questions</h2>
            
            <div class="space-y-6">
                <div>
                    <h3 class="font-bold text-lg">Q: Can I cancel the deletion after submitting the request?</h3>
                    <p>A: Yes, but only if you contact us within 7 days of submission. After 7 days, the deletion process cannot be reversed.</p>
                </div>

                <div>
                    <h3 class="font-bold text-lg">Q: Will my username be available after deletion?</h3>
                    <p>A: Yes, your username will be released and available for new registrations after 30 days.</p>
                </div>

                <div>
                    <h3 class="font-bold text-lg">Q: What if I need my order history for taxes?</h3>
                    <p>A: Download your order history before deleting your account. We can provide anonymized transaction records if needed.</p>
                </div>

                <div>
                    <h3 class="font-bold text-lg">Q: Can I delete specific orders instead of the entire account?</h3>
                    <p>A: Contact our support team at eh202202743@wmsu.edu.ph to discuss options for selective data deletion.</p>
                </div>
            </div>

            <h2 class="text-2xl font-bold mt-8 mb-4">Need Help?</h2>
            <p>
                If you have questions about the data deletion process or need assistance, please contact us:
            </p>
            <div class="bg-red-50 p-6 rounded mt-4">
                <p class="font-semibold mb-2">Email Support:</p>
                <p class="text-lg">
                    <a href="mailto:eh202202743@wmsu.edu.ph" class="text-red-600 hover:underline">eh202202743@wmsu.edu.ph</a>
                </p>
                <p class="font-semibold mt-4 mb-2">Response Time:</p>
                <p>We typically respond within 24-48 business hours</p>
            </div>

            <p class="text-gray-600 text-sm mt-12 border-t pt-4">
                <strong>Last updated:</strong> {{ now()->format('F d, Y') }}<br>
                <strong>Yakan E-commerce Platform</strong>
            </p>
        </div>
    </div>
</div>
@endsection
