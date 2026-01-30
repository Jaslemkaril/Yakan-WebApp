@extends('layouts.admin')

@section('title', 'Edit Coupon')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl md:text-3xl font-bold text-gray-900">Edit Coupon</h1>
            <p class="text-gray-600 mt-2">Update promotional code details</p>
        </div>
        <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center px-4 py-2 text-gray-700 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i>Back to Coupons
        </a>
    </div>

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex">
                <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3 mt-0.5"></i>
                <div>
                    <h3 class="text-red-800 font-semibold mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="p-8 space-y-6">
            @csrf
            @method('PATCH')

            <!-- Basic Information -->
            <div class="border-b pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Coupon Code *</label>
                        <input type="text" name="code" value="{{ old('code', $coupon->code) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required />
                        @error('code')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                        <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="percent" @selected(old('type', $coupon->type)==='percent')>Percentage (%)</option>
                            <option value="fixed" @selected(old('type', $coupon->type)==='fixed')>Fixed Amount (₱)</option>
                        </select>
                        @error('type')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <!-- Discount Details -->
            <div class="border-b pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Discount Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Discount Value *</label>
                        <input type="number" step="0.01" min="0" name="value" value="{{ old('value', $coupon->value) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required />
                        @error('value')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Discount (optional)</label>
                        <input type="number" step="0.01" min="0" name="max_discount" value="{{ old('max_discount', $coupon->max_discount) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                        @error('max_discount')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Spend (₱)</label>
                        <input type="number" step="0.01" min="0" name="min_spend" value="{{ old('min_spend', $coupon->min_spend) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                        @error('min_spend')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Usage Limit (optional)</label>
                        <input type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                        @error('usage_limit')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Per-User Limit (optional)</label>
                        <input type="number" min="1" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                        @error('usage_limit_per_user')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <!-- Validity Period -->
            <div class="border-b pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Validity Period</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Starts At (optional)</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                        @error('starts_at')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ends At (optional)</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($coupon->ends_at)->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                        @error('ends_at')<span class="text-red-600 text-sm mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input type="checkbox" id="active" name="active" value="1" class="w-4 h-4 text-purple-600 rounded focus:ring-2 focus:ring-purple-500" @checked(old('active', $coupon->active)) />
                <label for="active" class="text-sm font-medium text-gray-700">Keep this coupon active</label>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-6 border-t">
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-purple-800 transition-all duration-300">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
