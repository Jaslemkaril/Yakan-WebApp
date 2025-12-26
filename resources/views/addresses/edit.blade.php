@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-maroon-50 via-white to-maroon-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="mb-10">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-lg" style="background-color:#800000;">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900">Edit Address</h1>
                        <p class="text-gray-600 mt-1">Update your delivery address information</p>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Form Header -->
                <div class="bg-gradient-to-r from-maroon-50 to-maroon-100 px-8 py-6 border-b-2" style="border-bottom-color:#e0b0b0;">
                    <p class="text-gray-700 font-medium">
                        <span style="color:#800000;">*</span> indicates required fields
                    </p>
                </div>

                <!-- Form Content -->
                <form action="{{ route('addresses.update', $address) }}" method="POST" class="p-8">
                    @csrf
                    @method('PUT')

                    <!-- Label -->
                    <div class="mb-8">
                        <label for="label" class="block text-gray-900 font-bold mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Address Label <span style="color:#800000;">*</span>
                        </label>
                        <select name="label" id="label" class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-all duration-300 @error('label') border-red-500 @else border-gray-300 @enderror" style="@error('label') @else border-color:#e0b0b0; @enderror focus:border-color:#800000;" required>
                            <option value="">Select a label</option>
                            <option value="Home" {{ old('label', $address->label) === 'Home' ? 'selected' : '' }}>🏠 Home</option>
                            <option value="Office" {{ old('label', $address->label) === 'Office' ? 'selected' : '' }}>🏢 Office</option>
                            <option value="Other" {{ old('label', $address->label) === 'Other' ? 'selected' : '' }}>📍 Other</option>
                        </select>
                        @error('label')
                            <p class="text-red-500 text-sm mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Full Name -->
                    <div class="mb-8">
                        <label for="full_name" class="block text-gray-900 font-bold mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Full Name <span style="color:#800000;">*</span>
                        </label>
                        <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $address->full_name) }}" class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-all duration-300 @error('full_name') border-red-500 @else border-gray-300 @enderror" style="@error('full_name') @else border-color:#e0b0b0; @enderror" placeholder="Enter your full name" required>
                        @error('full_name')
                            <p class="text-red-500 text-sm mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-8">
                        <label for="phone_number" class="block text-gray-900 font-bold mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 00.948-.684l1.498-4.493a1 1 0 011.502-.684l1.498 4.493a1 1 0 00.948.684H19a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/>
                            </svg>
                            Phone Number <span style="color:#800000;">*</span>
                        </label>
                        <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', $address->phone_number) }}" class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-all duration-300 @error('phone_number') border-red-500 @else border-gray-300 @enderror" style="@error('phone_number') @else border-color:#e0b0b0; @enderror" placeholder="09XXXXXXXXX" required>
                        @error('phone_number')
                            <p class="text-red-500 text-sm mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Street Address -->
                    <div class="mb-8">
                        <label for="street" class="block text-gray-900 font-bold mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5.581m0 0H9m5.581 0a2.121 2.121 0 01-4.242 0m9.242 0H15"/>
                            </svg>
                            Street Address <span style="color:#800000;">*</span>
                        </label>
                        <input type="text" name="street" id="street" value="{{ old('street', $address->street) }}" class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-all duration-300 @error('street') border-red-500 @else border-gray-300 @enderror" style="@error('street') @else border-color:#e0b0b0; @enderror" placeholder="House number, street name" required>
                        @error('street')
                            <p class="text-red-500 text-sm mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Barangay -->
                    <div class="mb-8">
                        <label for="barangay" class="block text-gray-900 font-bold mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.553-.894L9 7.882"/>
                            </svg>
                            Barangay
                        </label>
                        <input type="text" name="barangay" id="barangay" value="{{ old('barangay', $address->barangay) }}" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none transition-all duration-300" style="border-color:#e0b0b0;" placeholder="Barangay name">
                        @error('barangay')
                            <p class="text-red-500 text-sm mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- City -->
                    <div class="mb-8">
                        <label for="city" class="block text-gray-900 font-bold mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            City <span style="color:#800000;">*</span>
                        </label>
                        <input type="text" name="city" id="city" value="{{ old('city', $address->city) }}" class="w-full px-4 py-3 border-2 rounded-lg focus:outline-none transition-all duration-300 @error('city') border-red-500 @else border-gray-300 @enderror" style="@error('city') @else border-color:#e0b0b0; @enderror" placeholder="City or municipality" required>
                        @error('city')
                            <p class="text-red-500 text-sm mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Province -->
                    <div class="mb-8">
                        <label for="province" class="block text-gray-900 font-bold mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h6a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/>
                            </svg>
                            Province
                        </label>
                        <input type="text" name="province" id="province" value="{{ old('province', $address->province) }}" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none transition-all duration-300" style="border-color:#e0b0b0;" placeholder="Province name">
                        @error('province')
                            <p class="text-red-500 text-sm mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Postal Code -->
                    <div class="mb-8">
                        <label for="postal_code" class="block text-gray-900 font-bold mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3v-6"/>
                            </svg>
                            Postal Code
                        </label>
                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $address->postal_code) }}" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none transition-all duration-300" style="border-color:#e0b0b0;" placeholder="ZIP code">
                        @error('postal_code')
                            <p class="text-red-500 text-sm mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Set as Default -->
                    <div class="mb-10 p-4 rounded-lg" style="background-color:#f5e6e8;">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_default" value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }} class="w-5 h-5 rounded" style="accent-color:#800000;">
                            <span class="ml-3 text-gray-900 font-semibold flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Set as default address
                            </span>
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 px-6 py-3 text-white rounded-lg font-bold transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1 flex items-center justify-center" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Update Address
                        </button>
                        <a href="{{ route('addresses.index') }}" class="flex-1 px-6 py-3 text-gray-900 rounded-lg font-bold transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1 flex items-center justify-center bg-gray-200" onmouseover="this.style.backgroundColor='#d1d5db'" onmouseout="this.style.backgroundColor='#e5e7eb'">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bg-maroon-50 { background-color: #faf5f5; }
    .bg-maroon-100 { background-color: #f5e6e8; }

    form {
        animation: slideDown 0.4s ease-out;
    }

    input:focus, select:focus {
        box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
    }
</style>
@endsection
