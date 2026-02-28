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
    const regionSelect   = document.getElementById('edit_region_id');
    const provinceSelect = document.getElementById('edit_province_id');
    const citySelect     = document.getElementById('edit_city_id');
    const barangaySelect = document.getElementById('edit_barangay_id');

    const urlParams = new URLSearchParams(window.location.search);
    const authToken = urlParams.get('auth_token') || sessionStorage.getItem('auth_token') || '';
    function apiUrl(path) {
        return authToken ? `${path}?auth_token=${encodeURIComponent(authToken)}` : path;
    }

    // IDs resolved server-side
    const preselect = {
        region_id:   {{ $region->id   ?? 'null' }},
        province_id: {{ $province->id ?? 'null' }},
        city_id:     {{ $city->id     ?? 'null' }},
        barangay_id: {{ $barangay->id ?? 'null' }},
    };

    function setLoading(select, loading) {
        select.disabled = loading;
        select.classList.toggle('bg-gray-100', loading);
    }

    // Step 1 – load all regions, select current
    function loadRegions() {
        fetch(apiUrl('/addresses/api/regions'))
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                regionSelect.innerHTML = '<option value="">-- Select Region --</option>';
                data.data.forEach(region => {
                    const opt = new Option(region.name, region.id, false, region.id == preselect.region_id);
                    regionSelect.add(opt);
                });
                if (preselect.province_id) loadProvinces(preselect.region_id);
            })
            .catch(e => console.error('Error loading regions:', e));
    }

    // Step 2 – load provinces for selected region, select current
    function loadProvinces(regionId) {
        setLoading(provinceSelect, true);
        fetch(apiUrl(`/addresses/api/provinces/${regionId}`))
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
                data.data.forEach(p => {
                    const opt = new Option(p.name, p.id, false, p.id == preselect.province_id);
                    provinceSelect.add(opt);
                });
                setLoading(provinceSelect, false);
                if (preselect.city_id) loadCities(preselect.province_id);
            })
            .catch(e => { console.error('Error loading provinces:', e); setLoading(provinceSelect, false); });
    }

    // Step 3 – load cities for selected province, select current
    function loadCities(provinceId) {
        setLoading(citySelect, true);
        fetch(apiUrl(`/addresses/api/cities/${provinceId}`))
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                citySelect.innerHTML = '<option value="">-- Select City/Municipality --</option>';
                data.data.forEach(c => {
                    const opt = new Option(c.name, c.id, false, c.id == preselect.city_id);
                    citySelect.add(opt);
                });
                setLoading(citySelect, false);
                if (preselect.barangay_id) loadBarangays(preselect.city_id);
            })
            .catch(e => { console.error('Error loading cities:', e); setLoading(citySelect, false); });
    }

    // Step 4 – load barangays for selected city, select current
    function loadBarangays(cityId) {
        setLoading(barangaySelect, true);
        fetch(apiUrl(`/addresses/api/barangays/${cityId}`))
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
                data.data.forEach(b => {
                    const opt = new Option(b.name, b.id, false, b.id == preselect.barangay_id);
                    barangaySelect.add(opt);
                });
                setLoading(barangaySelect, false);
            })
            .catch(e => { console.error('Error loading barangays:', e); setLoading(barangaySelect, false); });
    }

    // Chain on manual change
    regionSelect.addEventListener('change', function() {
        provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
        citySelect.innerHTML = '<option value="">-- Select City/Municipality --</option>';
        barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
        if (this.value) loadProvinces(this.value);
    });
    provinceSelect.addEventListener('change', function() {
        citySelect.innerHTML = '<option value="">-- Select City/Municipality --</option>';
        barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
        if (this.value) loadCities(this.value);
    });
    citySelect.addEventListener('change', function() {
        barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
        if (this.value) loadBarangays(this.value);
    });

    // Kick off
    loadRegions();
})();
</script>
