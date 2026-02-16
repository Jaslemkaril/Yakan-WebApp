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
        <label class="block text-sm font-medium text-gray-700 mb-2">Region</label>
        <select name="region_id" id="edit_region_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
            <option value="">-- Select Region --</option>
        </select>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
        <select name="province_id" id="edit_province_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
            <option value="">-- Select Province --</option>
        </select>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
        <select name="city_id" id="edit_city_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
            <option value="">-- Select City/Municipality --</option>
        </select>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
        <select name="barangay_id" id="edit_barangay_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
            <option value="">-- Select Barangay --</option>
        </select>
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

<script>
(function() {
    const regionSelect = document.getElementById('edit_region_id');
    const provinceSelect = document.getElementById('edit_province_id');
    const citySelect = document.getElementById('edit_city_id');
    const barangaySelect = document.getElementById('edit_barangay_id');
    
    // Store current address data
    const currentAddress = {
        province: "{{ $address->province }}",
        city: "{{ $address->city }}",
        barangay: "{{ $address->barangay }}"
    };
    
    // Load regions on form load
    loadEditRegions();
    
    // Region change event
    regionSelect.addEventListener('change', function() {
        const regionId = this.value;
        resetDropdown(provinceSelect, 'Select Province');
        resetDropdown(citySelect, 'Select City/Municipality');
        resetDropdown(barangaySelect, 'Select Barangay');
        
        if (regionId) {
            loadEditProvinces(regionId);
        }
    });
    
    // Province change event
    provinceSelect.addEventListener('change', function() {
        const provinceId = this.value;
        resetDropdown(citySelect, 'Select City/Municipality');
        resetDropdown(barangaySelect, 'Select Barangay');
        
        if (provinceId) {
            loadEditCities(provinceId);
        }
    });
    
    // City change event
    citySelect.addEventListener('change', function() {
        const cityId = this.value;
        resetDropdown(barangaySelect, 'Select Barangay');
        
        if (cityId) {
            loadEditBarangays(cityId);
        }
    });
    
    // Load regions
    function loadEditRegions() {
        fetch('/addresses/api/regions')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    regionSelect.innerHTML = '<option value="">-- Select Region --</option>';
                    data.data.forEach(region => {
                        const option = document.createElement('option');
                        option.value = region.id;
                        option.textContent = region.name;
                        regionSelect.appendChild(option);
                    });
                    
                    // Auto-load provinces to find the current one
                    findAndSelectProvince();
                }
            })
            .catch(error => console.error('Error loading regions:', error));
    }
    
    // Find and select province
    function findAndSelectProvince() {
        // Try each region to find the province
        fetch('/addresses/api/regions')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let found = false;
                    const checkRegion = (index) => {
                        if (index >= data.data.length || found) return;
                        
                        const region = data.data[index];
                        fetch(`/addresses/api/provinces/${region.id}`)
                            .then(response => response.json())
                            .then(provinceData => {
                                const matchingProvince = provinceData.data.find(p => p.name === currentAddress.province);
                                if (matchingProvince) {
                                    found = true;
                                    regionSelect.value = region.id;
                                    loadEditProvinces(region.id, matchingProvince.id);
                                } else {
                                    checkRegion(index + 1);
                                }
                            });
                    };
                    checkRegion(0);
                }
            });
    }
    
    // Load provinces
    function loadEditProvinces(regionId, selectProvinceId = null) {
        fetch(`/addresses/api/provinces/${regionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
                    data.data.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.id;
                        option.textContent = province.name;
                        if (selectProvinceId && province.id === selectProvinceId) {
                            option.selected = true;
                        } else if (!selectProvinceId && province.name === currentAddress.province) {
                            option.selected = true;
                            loadEditCities(province.id);
                        }
                        provinceSelect.appendChild(option);
                    });
                    
                    if (selectProvinceId) {
                        loadEditCities(selectProvinceId);
                    }
                }
            })
            .catch(error => console.error('Error loading provinces:', error));
    }
    
    // Load cities
    function loadEditCities(provinceId) {
        fetch(`/addresses/api/cities/${provinceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    citySelect.innerHTML = '<option value="">-- Select City/Municipality --</option>';
                    data.data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id;
                        option.textContent = city.name;
                        if (city.name === currentAddress.city) {
                            option.selected = true;
                            loadEditBarangays(city.id);
                        }
                        citySelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading cities:', error));
    }
    
    // Load barangays
    function loadEditBarangays(cityId) {
        fetch(`/addresses/api/barangays/${cityId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
                    data.data.forEach(barangay => {
                        const option = document.createElement('option');
                        option.value = barangay.id;
                        option.textContent = barangay.name;
                        if (barangay.name === currentAddress.barangay) {
                            option.selected = true;
                        }
                        barangaySelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading barangays:', error));
    }
    
    // Reset dropdown helper
    function resetDropdown(selectElement, placeholder) {
        selectElement.innerHTML = `<option value="">-- ${placeholder} --</option>`;
    }
})();
</script>
