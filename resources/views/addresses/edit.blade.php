<form action="{{ route('addresses.update', $address) }}" method="POST" class="p-6">
    @csrf
    @method('PATCH')
    
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
            <input type="text" name="full_name" value="{{ old('full_name', $address->full_name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
            <input type="tel" name="phone_number" value="{{ old('phone_number', $address->phone_number) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
        </div>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Region, Province, City, Barangay</label>
        <input type="text" name="region" value="{{ old('region', $address->province . ', ' . $address->city . ($address->barangay ? ', ' . $address->barangay : '')) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Street Name, Building, House No.</label>
        <input type="text" name="formatted_address" value="{{ old('formatted_address', $address->street) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
    </div>
    
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Label As:</label>
        <div class="flex gap-3">
            <label class="flex items-center">
                <input type="radio" name="label" value="Home" {{ old('label', $address->label) === 'Home' ? 'checked' : '' }} class="mr-2 text-[#8B1A1A] focus:ring-[#8B1A1A]">
                <span class="text-gray-700">Home</span>
            </label>
            <label class="flex items-center">
                <input type="radio" name="label" value="Work" {{ old('label', $address->label) === 'Work' ? 'checked' : '' }} class="mr-2 text-[#8B1A1A] focus:ring-[#8B1A1A]">
                <span class="text-gray-700">Work</span>
            </label>
        </div>
    </div>
    
    <div class="flex gap-3 pt-4 border-t border-gray-200">
        <button type="button" onclick="document.getElementById('editAddressModal').classList.add('hidden')" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            Cancel
        </button>
        <button type="submit" class="flex-1 px-6 py-3 bg-[#8B1A1A] text-white rounded-lg hover:bg-[#6B1414] transition-colors">
            Submit
        </button>
    </div>
</form>
