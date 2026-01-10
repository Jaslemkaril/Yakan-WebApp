@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-maroon-700 to-maroon-800 shadow-2xl" style="background: linear-gradient(to right, #800000, #600000);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center">
                <div>
                    <h1 class="text-3xl font-black text-white">System Settings</h1>
                    <p class="text-maroon-100 mt-2">Manage pricing and system configuration</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf

            <!-- Info Box -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                    <h4 class="font-semibold text-gray-800 mb-2">ℹ️ Pricing Information</h4>
                    <p class="text-sm text-gray-700">
                        <strong>Pattern prices are now managed individually for each pattern.</strong>
                    </p>
                    <p class="text-xs text-gray-600 mt-2">
                        • Each pattern has its own unique price<br>
                        • Set the price when creating or editing a pattern<br>
                        • No global price per meter or difficulty-based fees
                    </p>
                </div>
            </div>

            <!-- Production Timeline Settings -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-black text-gray-900 mb-6">Production Timeline</h2>
                <p class="text-gray-600 mb-6 text-sm">Configure quality check duration (shipping times are zone-based from Zamboanga City)</p>
                
                <div class="space-y-4">
                    <!-- Quality Check Days -->
                    <div class="p-4 bg-purple-50 border-l-4 border-purple-500 rounded">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Quality Check Days *</label>
                        <p class="text-xs text-gray-600 mb-3">How many days for quality check after design production</p>
                        <input type="number" min="1" max="30" name="quality_check_days" value="{{ $settings['quality_check_days'] }}" required 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" />
                        @error('quality_check_days') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-6 p-4 bg-gray-100 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">⏱️ Timeline Formula</h4>
                    <p class="text-sm text-gray-700 mb-2">
                        <strong>Estimated Delivery = Production Days + Quality Check Days + Shipping Days</strong>
                    </p>
                    <p class="text-xs text-gray-600 mt-2">
                        <strong>Shipping Days (Zone-based from Zamboanga City):</strong><br>
                        • Zamboanga City: 1 day<br>
                        • Zamboanga Peninsula: 2 days<br>
                        • Western Mindanao: 3 days<br>
                        • Other Mindanao: 4 days<br>
                        • Visayas: 4 days<br>
                        • Metro Manila: 5 days<br>
                        • Northern Luzon: 6 days<br>
                        • Remote/Far Areas: 7 days
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('admin.dashboard') }}" class="px-6 py-3 bg-gray-200 text-gray-700 font-black rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-3 text-white font-black rounded-lg hover:opacity-90 transition-all shadow-lg" style="background-color: #800000;">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
