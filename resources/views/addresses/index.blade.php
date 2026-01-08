@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Back Button and Header -->
            <div class="mb-8">
                <button onclick="window.history.back()" class="mb-4 flex items-center gap-2 text-gray-600 hover:text-[#8B1A1A] transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="font-medium">Back</span>
                </button>
                
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">My Addresses</h1>
                    <button onclick="document.getElementById('addAddressModal').classList.remove('hidden')" class="px-6 py-3 bg-[#8B1A1A] text-white font-medium rounded-lg hover:bg-[#6B1414] transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add New Address
                    </button>
                </div>
            </div>

            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow-md">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Address Section Title -->
            <div class="mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Address</h2>
            </div>

            <!-- Addresses List -->
            @if ($addresses->count() > 0)
                <div class="space-y-4">
                    @foreach ($addresses as $address)
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-bold text-gray-900">{{ $address->full_name }}</h3>
                                        <span class="text-gray-600">|</span>
                                        <span class="text-gray-700">({{ $address->phone_number }})</span>
                                    </div>
                                    
                                    <p class="text-gray-700 mb-3">{{ $address->street }}</p>
                                    <p class="text-gray-600">
                                        @if($address->barangay){{ $address->barangay }}, @endif
                                        {{ $address->city }}, {{ $address->province }}, 
                                        @if($address->postal_code){{ $address->postal_code }}@endif
                                    </p>
                                    
                                    <div class="flex gap-2 mt-3">
                                        @if ($address->is_default)
                                            <span class="px-3 py-1 text-xs border border-[#8B1A1A] text-[#8B1A1A] rounded">Default</span>
                                        @endif
                                        @if ($address->label === 'Home')
                                            <span class="px-3 py-1 text-xs border border-gray-400 text-gray-700 rounded">Pickup Address</span>
                                        @endif
                                        @if ($address->label)
                                            <span class="px-3 py-1 text-xs border border-gray-400 text-gray-700 rounded">Return Address</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex items-start gap-2 ml-4">
                                    <button onclick="editAddress({{ $address->id }})" class="text-[#8B1A1A] hover:text-[#6B1414] font-medium">Edit</button>
                                    @if (!$address->is_default)
                                        <span class="text-gray-300">|</span>
                                        <form action="{{ route('addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-600 hover:text-red-600 font-medium">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            
                            @if (!$address->is_default)
                                <div class="mt-4 pt-4 border-t border-gray-200 text-right">
                                    <form action="{{ route('addresses.setDefault', $address) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-6 py-2 border border-gray-300 text-gray-700 rounded hover:border-[#8B1A1A] hover:text-[#8B1A1A] transition-colors">
                                            Set as default
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg shadow-md border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-gray-600 text-lg mb-6">No addresses saved yet</p>
                    <button onclick="document.getElementById('addAddressModal').classList.remove('hidden')" class="px-8 py-3 bg-[#8B1A1A] text-white rounded-lg hover:bg-[#6B1414] transition-all duration-200 shadow-md">
                        Add Your First Address
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div id="addAddressModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">New Address</h2>
                <button onclick="document.getElementById('addAddressModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <form action="{{ route('addresses.store') }}" method="POST" class="p-6">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" name="full_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" name="phone_number" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Region, Province, City, Barangay</label>
                <input type="text" name="region" placeholder="Mindanao, Zamboanga Del Sur, Zamboanga City, Tumaga" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                <input type="text" name="postal_code" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Street Name, Building, House No.</label>
                <input type="text" name="formatted_address" placeholder="RRM Perez Drive, (papasok na pababa, sa ika limang poste sa side ng silver gate ng tower) Sun Street, Tumaga, Zamboanga City" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Label As:</label>
                <div class="flex gap-3">
                    <label class="flex items-center">
                        <input type="radio" name="label" value="Home" class="mr-2 text-[#8B1A1A] focus:ring-[#8B1A1A]">
                        <span class="text-gray-700">Home</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="label" value="Work" class="mr-2 text-[#8B1A1A] focus:ring-[#8B1A1A]">
                        <span class="text-gray-700">Work</span>
                    </label>
                </div>
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="document.getElementById('addAddressModal').classList.add('hidden')" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-[#8B1A1A] text-white rounded-lg hover:bg-[#6B1414] transition-colors">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Address Modal (will be populated dynamically) -->
<div id="editAddressModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">Edit Address</h2>
                <button onclick="document.getElementById('editAddressModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div id="editFormContainer"></div>
    </div>
</div>

<script>
function editAddress(addressId) {
    // Fetch address data and populate edit modal
    fetch(`/addresses/${addressId}/edit`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editFormContainer').innerHTML = html;
            document.getElementById('editAddressModal').classList.remove('hidden');
        });
}
</script>

@endsection
