@extends('layouts.app')

@section('content')
<div class="container py-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-8 text-red-800">Data Deletion Instructions</h1>
        
        <div class="prose prose-lg">
            <h2 class="text-2xl font-bold mt-8 mb-4">How to Delete Your Account and Data</h2>
            
            <h3 class="text-xl font-bold mt-6 mb-3">Option 1: Self-Service Deletion</h3>
            <ol class="list-decimal list-inside space-y-2">
                <li>Log in to your Yakan account</li>
                <li>Go to Account Settings</li>
                <li>Scroll to the bottom</li>
                <li>Click "Delete My Account"</li>
                <li>Enter your password</li>
                <li>Confirm deletion</li>
                <li>Check your email for confirmation</li>
            </ol>

            <h3 class="text-xl font-bold mt-6 mb-3">Option 2: Request via Email</h3>
            <p>
                Send an email to: <a href="mailto:eh202202743@wmsu.edu.ph" class="text-red-600 hover:underline"><strong>eh202202743@wmsu.edu.ph</strong></a>
            </p>
            <p>
                Include:
            </p>
            <ul class="list-disc list-inside">
                <li>Your full name</li>
                <li>Email address of your account</li>
                <li>Subject: "Data Deletion Request"</li>
            </ul>
            <p class="text-red-600 font-semibold mt-4">
                We will process your request within 30 days.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4">What Gets Deleted</h2>
            <ul class="list-disc list-inside space-y-2">
                <li>Personal profile information (name, email, phone)</li>
                <li>Account credentials and password</li>
                <li>Order history and receipts</li>
                <li>Payment information</li>
                <li>Wishlist and saved items</li>
                <li>Address book</li>
                <li>Session data</li>
                <li>Social media connections</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4">What We Keep (Legal Reasons)</h2>
            <ul class="list-disc list-inside space-y-2">
                <li><strong>Transaction Records:</strong> Anonymized only, for accounting</li>
                <li><strong>Tax Records:</strong> Invoices (required by law)</li>
                <li><strong>Fraud Prevention:</strong> Anonymized data</li>
                <li><strong>Legal Compliance:</strong> Government required records</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4">Timeline</h2>
            <table class="w-full border-collapse border border-gray-300 mt-4">
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 p-2 text-left">Step</th>
                    <th class="border border-gray-300 p-2 text-left">Timeline</th>
                </tr>
                <tr>
                    <td class="border border-gray-300 p-2">Request Submitted</td>
                    <td class="border border-gray-300 p-2">Immediately</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 p-2">Processing</td>
                    <td class="border border-gray-300 p-2">1-7 days</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 p-2">Deletion Complete</td>
                    <td class="border border-gray-300 p-2">Within 30 days</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 p-2">Confirmation Email</td>
                    <td class="border border-gray-300 p-2">Within 30 days</td>
                </tr>
            </table>

            <h2 class="text-2xl font-bold mt-8 mb-4">Questions?</h2>
            <p>
                Contact: <a href="mailto:eh202202743@wmsu.edu.ph" class="text-red-600 hover:underline"><strong>eh202202743@wmsu.edu.ph</strong></a>
            </p>

            <p class="text-gray-600 text-sm mt-12 border-t pt-4">
                <strong>Last updated:</strong> {{ now()->format('F d, Y') }}<br>
                <strong>Yakan E-commerce Platform</strong>
            </p>
        </div>
    </div>
</div>
@endsection
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

            <h2 class="text-2xl font-bold mt-8 mb-4">What Gets Deleted</h2>
            <ul class="list-disc list-inside space-y-2">
                <li>Personal profile information</li>
                <li>Account credentials</li>
                <li>Wishlist and saved items</li>
                <li>Addresses and preferences</li>
                <li>Social media connections</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4">What We Keep</h2>
            <p class="text-sm text-gray-600">
                For legal compliance, we retain anonymized transaction records.
            </p>

            <p class="text-gray-600 text-sm mt-12 border-t pt-4">
                <strong>Last updated:</strong> {{ now()->format('F d, Y') }}<br>
                <strong>Yakan E-commerce Platform</strong>
            </p>
        </div>
    </div>
</div>
@endsection
