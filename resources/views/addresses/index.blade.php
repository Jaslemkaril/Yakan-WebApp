@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-maroon-50 via-white to-maroon-50">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-5xl mx-auto">
            <!-- Enhanced Header -->
            <div class="mb-12">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-lg" style="background-color:#800000;">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-4xl font-bold text-gray-900">My Addresses</h1>
                                <p class="text-gray-600 mt-1">Manage your delivery locations</p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('addresses.create') }}" class="group relative px-8 py-3 text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add New Address
                        </span>
                    </a>
                </div>
            </div>

            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-8 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg p-4 shadow-md" style="border-left-color:#10b981;">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Addresses List -->
            @if ($addresses->count() > 0)
                <div class="grid gap-6">
                    @foreach ($addresses as $address)
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border-l-4 {{ $address->is_default ? 'border-maroon-600' : 'border-gray-200' }}" style="{{ $address->is_default ? 'border-left-color:#800000;' : '' }}">
                            <!-- Card Header -->
                            <div class="bg-gradient-to-r {{ $address->is_default ? 'from-maroon-50 to-maroon-100' : 'from-gray-50 to-gray-100' }} px-6 py-4 border-b {{ $address->is_default ? 'border-maroon-200' : 'border-gray-200' }}">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 flex-wrap">
                                            <h3 class="text-2xl font-bold text-gray-900">{{ $address->full_name }}</h3>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold" style="background-color:#f5e6e8; color:#800000;">
                                                {{ $address->label }}
                                            </span>
                                            @if ($address->is_default)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold text-white" style="background-color:#800000;">
                                                    ★ Default Address
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-gray-600 mt-2 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 00.948-.684l1.498-4.493a1 1 0 011.502-.684l1.498 4.493a1 1 0 00.948.684H19a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/>
                                            </svg>
                                            {{ $address->phone_number }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Details -->
                            <div class="px-6 py-5">
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5.581m0 0H9m5.581 0a2.121 2.121 0 01-4.242 0m9.242 0H15"/>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-gray-600">Street Address</p>
                                            <p class="text-gray-900 font-semibold">{{ $address->street }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @if ($address->barangay)
                                            <div class="flex items-start">
                                                <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 003 16.382V5.618a1 1 0 011.553-.894L9 7.882"/>
                                                </svg>
                                                <div>
                                                    <p class="text-sm text-gray-600">Barangay</p>
                                                    <p class="text-gray-900 font-semibold">{{ $address->barangay }}</p>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            <div>
                                                <p class="text-sm text-gray-600">City</p>
                                                <p class="text-gray-900 font-semibold">{{ $address->city }}</p>
                                            </div>
                                        </div>
                                        
                                        @if ($address->province)
                                            <div class="flex items-start">
                                                <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h6a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/>
                                                </svg>
                                                <div>
                                                    <p class="text-sm text-gray-600">Province</p>
                                                    <p class="text-gray-900 font-semibold">{{ $address->province }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if ($address->postal_code)
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3v-6"/>
                                            </svg>
                                            <div>
                                                <p class="text-sm text-gray-600">Postal Code</p>
                                                <p class="text-gray-900 font-semibold">{{ $address->postal_code }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex gap-3 flex-wrap">
                                <a href="{{ route('addresses.edit', $address) }}" class="flex-1 min-w-max px-4 py-2 text-white rounded-lg font-semibold transition-all duration-300 transform hover:scale-105 flex items-center justify-center" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </a>
                                @if (!$address->is_default)
                                    <form action="{{ route('addresses.setDefault', $address) }}" method="POST" class="flex-1 min-w-max">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2 text-white rounded-lg font-semibold transition-all duration-300 transform hover:scale-105 flex items-center justify-center" style="background-color:#a0a0a0;" onmouseover="this.style.backgroundColor='#808080'" onmouseout="this.style.backgroundColor='#a0a0a0'">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            Set Default
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('addresses.destroy', $address) }}" method="POST" class="flex-1 min-w-max" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full px-4 py-2 text-white rounded-lg font-semibold transition-all duration-300 transform hover:scale-105 flex items-center justify-center bg-red-500" onmouseover="this.style.backgroundColor='#dc2626'" onmouseout="this.style.backgroundColor='#ef4444'">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center border-2 border-dashed" style="border-color:#e0b0b0;">
                    <div class="mb-6 flex justify-center">
                        <div class="w-20 h-20 rounded-full flex items-center justify-center" style="background-color:#f5e6e8;">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-gray-600 text-lg mb-6 font-medium">No addresses saved yet</p>
                    <p class="text-gray-500 mb-8">Start by adding your first delivery address</p>
                    <a href="{{ route('addresses.create') }}" class="inline-block px-8 py-3 text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Your First Address
                        </span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bg-maroon-50 { background-color: #faf5f5; }
    .bg-maroon-100 { background-color: #f5e6e8; }
    .border-maroon-200 { border-color: #e0b0b0; }
    .border-maroon-600 { border-color: #800000; }
    .text-maroon-700 { color: #8b3a56; }

    div[class*="grid gap-6"] > div {
        animation: slideIn 0.3s ease-out forwards;
    }

    div[class*="grid gap-6"] > div:nth-child(2) {
        animation-delay: 0.1s;
    }

    div[class*="grid gap-6"] > div:nth-child(3) {
        animation-delay: 0.2s;
    }
</style>
@endsection
