@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<!-- Loading Overlay -->
<div id="settings-loading" class="hidden fixed inset-0 z-50 flex flex-col items-center justify-center" style="background: rgba(90,0,0,0.88); backdrop-filter: blur(4px);">
    <div class="flex flex-col items-center gap-6">
        <div class="w-16 h-16 border-4 border-white/30 border-t-white rounded-full animate-spin"></div>
        <div class="text-center">
            <p class="text-white text-xl font-black tracking-wide">Saving Settings...</p>
            <p class="text-red-200 text-sm mt-1">Please wait, do not close this page</p>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="settings-toast" class="hidden fixed top-6 right-6 z-50 flex items-center gap-3 px-5 py-4 rounded-xl shadow-2xl text-white text-sm font-semibold max-w-sm transition-all duration-300">
    <span id="settings-toast-icon" class="text-xl"></span>
    <span id="settings-toast-msg"></span>
</div>
<div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="shadow-lg" style="background: linear-gradient(135deg, #800000 0%, #5a0000 100%);">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-white tracking-tight">System Settings</h1>
                    <p class="text-red-200 mt-1 text-sm">Configure payment details, production timelines, and system preferences</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6 flex items-center gap-3 p-4 bg-green-50 border border-green-300 text-green-800 rounded-xl shadow-sm">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 flex items-center gap-3 p-4 bg-red-50 border border-red-300 text-red-800 rounded-xl shadow-sm">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}{{ request('auth_token') ? '?auth_token=' . urlencode(request('auth_token')) : '' }}" class="space-y-8" id="settings-form">
            @csrf
            @if(request('auth_token'))
                <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
            @endif

            <!-- ======================== GCASH SETTINGS ======================== -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">📱</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-gray-900">GCash Payment Details</h2>
                        <p class="text-xs text-gray-500 mt-0.5">This information is shown to customers when they choose GCash as payment method</p>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- GCash Number -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            GCash Number
                            <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input type="text" name="gcash_number" value="{{ old('gcash_number', $settings['gcash_number']) }}"
                                   placeholder="e.g. 09XX-XXX-XXXX"
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('gcash_number') border-red-400 @enderror">
                        </div>
                        @error('gcash_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1">The mobile number customers will send money to</p>
                    </div>

                    <!-- GCash Account Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            GCash Account Name
                            <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input type="text" name="gcash_name" value="{{ old('gcash_name', $settings['gcash_name']) }}"
                                   placeholder="e.g. Tuwas Yakan"
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('gcash_name') border-red-400 @enderror">
                        </div>
                        @error('gcash_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 mt-1">Full name displayed on the GCash account</p>
                    </div>
                </div>

                <!-- Preview -->
                <div class="mx-6 mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <p class="text-xs font-semibold text-blue-800 mb-2 uppercase tracking-wide">Customer Preview</p>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">G</div>
                        <div>
                            <p class="font-bold text-blue-900">GCash</p>
                            <p class="text-sm text-blue-700">
                                {{ $settings['gcash_number'] ?: '09XX-XXX-XXXX' }} · {{ $settings['gcash_name'] ?: 'Account Name' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================== BANK TRANSFER SETTINGS ======================== -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                        <span class="text-2xl">🏦</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-gray-900">Bank Transfer Details</h2>
                        <p class="text-xs text-gray-500 mt-0.5">This information is shown to customers when they choose Bank Transfer as payment method</p>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Bank Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Bank Name <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                </svg>
                            </div>
                            <input type="text" name="bank_name" value="{{ old('bank_name', $settings['bank_name']) }}"
                                   placeholder="e.g. BDO, BPI, Metrobank"
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('bank_name') border-red-400 @enderror">
                        </div>
                        @error('bank_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Account Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Account Name <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $settings['bank_account_name']) }}"
                                   placeholder="e.g. Tuwas Yakan"
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('bank_account_name') border-red-400 @enderror">
                        </div>
                        @error('bank_account_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Account Number -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Account Number <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $settings['bank_account_number']) }}"
                                   placeholder="e.g. 1234-5678-9012"
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl font-mono focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('bank_account_number') border-red-400 @enderror">
                        </div>
                        @error('bank_account_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Branch -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Branch <span class="text-gray-400 font-normal">(optional)</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="bank_branch" value="{{ old('bank_branch', $settings['bank_branch']) }}"
                                   placeholder="e.g. Zamboanga City Main"
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('bank_branch') border-red-400 @enderror">
                        </div>
                        @error('bank_branch') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Preview -->
                <div class="mx-6 mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                    <p class="text-xs font-semibold text-green-800 mb-2 uppercase tracking-wide">Customer Preview</p>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div><span class="text-gray-500">Bank:</span> <span class="font-semibold text-gray-900">{{ $settings['bank_name'] ?: '—' }}</span></div>
                        <div><span class="text-gray-500">Account Name:</span> <span class="font-semibold text-gray-900">{{ $settings['bank_account_name'] ?: '—' }}</span></div>
                        <div><span class="text-gray-500">Account No.:</span> <span class="font-mono font-semibold text-gray-900">{{ $settings['bank_account_number'] ?: '—' }}</span></div>
                        @if($settings['bank_branch'])<div><span class="text-gray-500">Branch:</span> <span class="font-semibold text-gray-900">{{ $settings['bank_branch'] }}</span></div>@endif
                    </div>
                </div>
            </div>

            <!-- ======================== PRODUCTION TIMELINE ======================== -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                    <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-gray-900">Production Timeline</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Set default custom-order estimate (2 weeks standard) and quality check duration</p>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Custom Order Estimated Days
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-3">Default estimate shown to customers and admin for custom orders (standard: 14 days)</p>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <input type="number" min="1" max="90" name="custom_order_estimated_days"
                                       value="{{ old('custom_order_estimated_days', $settings['custom_order_estimated_days']) }}" required
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('custom_order_estimated_days') border-red-400 @enderror">
                            </div>
                            @error('custom_order_estimated_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Quality Check Days
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-3">Number of days for quality check after design production</p>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="number" min="1" max="30" name="quality_check_days"
                                       value="{{ old('quality_check_days', $settings['quality_check_days']) }}" required
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('quality_check_days') border-red-400 @enderror">
                            </div>
                            @error('quality_check_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="bg-orange-50 rounded-xl p-4 border border-orange-200">
                            <p class="text-xs font-bold text-orange-800 mb-2 uppercase tracking-wide">⏱ Timeline Formula</p>
                            <p class="text-xs font-semibold text-orange-900 mb-3">Target = Order Date + Estimated Days (default 14)</p>
                            <p class="text-xs text-orange-700 font-semibold mb-2">Current default: {{ $settings['custom_order_estimated_days'] }} day{{ (int) $settings['custom_order_estimated_days'] === 1 ? '' : 's' }}</p>
                            <p class="text-xs font-semibold text-orange-900 mb-3">Detailed timeline still uses: Production Days + Quality Check Days + Shipping Days</p>
                            <p class="text-xs text-orange-700 font-semibold mb-1">Shipping Zones:</p>
                            <ul class="text-xs text-orange-700 space-y-0.5">
                                <li>• Zamboanga City: 1 day</li>
                                <li>• Zamboanga Peninsula: 2 days</li>
                                <li>• W. Mindanao: 3 days | Other Mindanao: 4 days</li>
                                <li>• Visayas: 4 days | Metro Manila: 5 days</li>
                                <li>• Northern Luzon: 6 days | Remote: 7 days</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================== ACTIONS ======================== -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Dashboard
                </a>
                <button type="submit"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 px-8 py-3 text-white font-black rounded-xl hover:opacity-90 active:scale-95 transition-all shadow-lg text-base"
                        style="background: linear-gradient(135deg, #800000 0%, #5a0000 100%);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save All Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('settings-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form   = e.target;
    const loader = document.getElementById('settings-loading');
    const toast  = document.getElementById('settings-toast');

    // Show loader
    loader.classList.remove('hidden');

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        const data = await response.json();

        // Hide loader
        loader.classList.add('hidden');

        // Show toast
        const toastMsg  = document.getElementById('settings-toast-msg');
        const toastIcon = document.getElementById('settings-toast-icon');

        if (data.success) {
            toast.style.background = '#166534';
            toastIcon.textContent  = '✓';
            toastMsg.textContent   = data.message;
        } else {
            toast.style.background = '#991b1b';
            toastIcon.textContent  = '✕';
            toastMsg.textContent   = data.message || 'Failed to save settings.';
        }

        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 4000);

    } catch (err) {
        loader.classList.add('hidden');
        const toast  = document.getElementById('settings-toast');
        document.getElementById('settings-toast-icon').textContent = '✕';
        document.getElementById('settings-toast-msg').textContent  = 'Network error. Please try again.';
        toast.style.background = '#991b1b';
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 4000);
    }
});
</script>
@endsection

