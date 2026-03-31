@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Add New Address</h1>
        @php
            $user = auth()->user();
            $prefillFirstName = old('first_name', $user->first_name ?? '');
            $prefillLastName = old('last_name', $user->last_name ?? '');

            if (($prefillFirstName === '' || $prefillLastName === '') && !empty($user?->name)) {
                $nameParts = preg_split('/\s+/', trim((string) $user->name));
                if ($prefillLastName === '' && count($nameParts) > 1) {
                    $prefillLastName = array_pop($nameParts);
                }
                if ($prefillFirstName === '') {
                    $prefillFirstName = implode(' ', $nameParts);
                }
            }
        @endphp

        <form action="{{ route('addresses.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-8">
            @csrf

            <!-- Label -->
            <div class="mb-6">
                <label for="label" class="block text-gray-700 font-bold mb-2">Address Label *</label>
                <select name="label" id="label" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('label') border-red-500 @enderror" required>
                    <option value="">Select a label</option>
                    <option value="Home" {{ old('label') === 'Home' ? 'selected' : '' }}>Home</option>
                    <option value="Office" {{ old('label') === 'Office' ? 'selected' : '' }}>Office</option>
                    <option value="Other" {{ old('label') === 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('label')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Name -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-gray-700 font-bold mb-2">First Name *</label>
                    <input type="text" name="first_name" id="first_name" value="{{ $prefillFirstName }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('first_name') border-red-500 @enderror" required>
                    @error('first_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-gray-700 font-bold mb-2">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" value="{{ $prefillLastName }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('last_name') border-red-500 @enderror" required>
                    @error('last_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Phone Number -->
            <div class="mb-6">
                <label for="phone_number" class="block text-gray-700 font-bold mb-2">Phone Number *</label>
                <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('phone_number') border-red-500 @enderror" required>
                @error('phone_number')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Street Address -->
            <div class="mb-6">
                <label for="street" class="block text-gray-700 font-bold mb-2">Street Address *</label>
                <input type="text" name="street" id="street" value="{{ old('street') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('street') border-red-500 @enderror" required>
                @error('street')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Barangay -->
            <div class="mb-6">
                <label for="barangay" class="block text-gray-700 font-bold mb-2">Barangay</label>
                <input type="text" name="barangay" id="barangay" value="{{ old('barangay') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                @error('barangay')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- City -->
            <div class="mb-6">
                <label for="city" class="block text-gray-700 font-bold mb-2">City *</label>
                <input type="text" name="city" id="city" value="{{ old('city') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 @error('city') border-red-500 @enderror" required>
                @error('city')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Province -->
            <div class="mb-6">
                <label for="province" class="block text-gray-700 font-bold mb-2">Province</label>
                <input type="text" name="province" id="province" value="{{ old('province') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                @error('province')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Postal Code -->
            <div class="mb-6">
                <label for="postal_code" class="block text-gray-700 font-bold mb-2">Postal Code</label>
                <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                @error('postal_code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Set as Default -->
            <div class="mb-8">
                <label class="flex items-center">
                    <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                    <span class="ml-2 text-gray-700">Set as default address</span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition flex-1">
                    Save Address
                </button>
                <a href="{{ route('addresses.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded-lg transition flex-1 text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
