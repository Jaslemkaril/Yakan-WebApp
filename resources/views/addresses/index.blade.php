@extends('layouts.app')

@section('content')
<!-- Toast Notification -->
<div id="toast" class="fixed top-6 right-6 z-[9999] hidden">
    <div class="flex items-center gap-3 bg-white border border-gray-200 shadow-xl rounded-xl px-5 py-4 min-w-[280px]">
        <div id="toastIcon" class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"></div>
        <p id="toastMsg" class="text-sm font-medium text-gray-800"></p>
    </div>
</div>

<div class="min-h-screen bg-gradient-to-br from-[#fdf6f0] to-[#f5ede4] py-10">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <button onclick="window.history.back()" class="w-10 h-10 flex items-center justify-center rounded-full bg-white shadow hover:shadow-md border border-gray-200 text-gray-500 hover:text-[#8B1A1A] transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">My Addresses</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Manage your saved delivery locations</p>
                    </div>
                </div>
                <button onclick="document.getElementById('addAddressModal').classList.remove('hidden')"
                    class="flex items-center gap-2 px-5 py-2.5 bg-[#8B1A1A] text-white text-sm font-semibold rounded-xl hover:bg-[#6B1414] transition-all shadow-md hover:shadow-lg active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Address
                </button>
            </div>

            @if (session('success'))
                <div id="sessionToast" class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-4 shadow-sm">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                    <button onclick="document.getElementById('sessionToast').remove()" class="ml-auto text-green-400 hover:text-green-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            <!-- Address Count -->
            @if ($addresses->count() > 0)
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">{{ $addresses->count() }} Saved {{ Str::plural('Address', $addresses->count()) }}</p>
            @endif

            <!-- Addresses List -->
            @if ($addresses->count() > 0)
                <div class="space-y-4" id="addressList">
                    @foreach ($addresses as $address)
                        <div class="address-card bg-white rounded-2xl border-2 {{ $address->is_default ? 'border-[#8B1A1A] shadow-md' : 'border-gray-100 shadow-sm' }} p-5 transition-all duration-200 hover:shadow-md"
                             id="address-card-{{ $address->id }}">

                            <!-- Top row: name + actions -->
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <!-- Icon -->
                                    <div class="mt-0.5 w-10 h-10 rounded-xl {{ $address->is_default ? 'bg-[#8B1A1A]' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0 transition-colors" id="icon-{{ $address->id }}">
                                        <svg class="w-5 h-5 {{ $address->is_default ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="iconSvg-{{ $address->id }}">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>

                                    <!-- Details -->
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span class="font-bold text-gray-900 text-base">{{ $address->full_name }}</span>
                                            <span class="text-gray-400 text-sm">·</span>
                                            <span class="text-gray-500 text-sm">{{ $address->phone_number }}</span>
                                        </div>
                                        <p class="text-gray-700 text-sm mb-1">{{ $address->street }}</p>
                                        <p class="text-gray-500 text-sm">
                                            @if($address->barangay){{ $address->barangay }}, @endif{{ $address->city }}, {{ $address->province }}@if($address->postal_code) {{ $address->postal_code }}@endif
                                        </p>
                                        <!-- Badges -->
                                        <div class="flex flex-wrap gap-1.5 mt-2" id="badges-{{ $address->id }}">
                                            @if ($address->is_default)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-[#8B1A1A] text-white">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                    Default
                                                </span>
                                            @endif
                                            @if ($address->label)
                                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                                    {{ $address->label === 'Home' ? '🏠' : '💼' }} {{ $address->label }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Action buttons -->
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button onclick="editAddress({{ $address->id }})"
                                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-[#8B1A1A] bg-[#8B1A1A]/5 hover:bg-[#8B1A1A]/10 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    @if (!$address->is_default)
                                        <button onclick="confirmDelete({{ $address->id }}, '{{ addslashes($address->full_name) }}')"
                                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>
                                        <form id="delete-form-{{ $address->id }}" action="{{ route('addresses.destroy', $address) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </div>
                            </div>

                            @if (!$address->is_default)
                                <!-- Set as Default button -->
                                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                                    <button onclick="setDefault({{ $address->id }})"
                                        id="setDefaultBtn-{{ $address->id }}"
                                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:border-[#8B1A1A] hover:text-[#8B1A1A] hover:bg-[#8B1A1A]/5 transition-all active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Set as Default
                                    </button>
                                    <form id="setDefaultForm-{{ $address->id }}" action="{{ route('addresses.setDefault', $address) }}" method="POST" class="hidden">
                                        @csrf
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty state -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
                    <div class="w-20 h-20 bg-[#8B1A1A]/5 rounded-full flex items-center justify-center mx-auto mb-5">
                        <svg class="w-10 h-10 text-[#8B1A1A]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">No addresses yet</h3>
                    <p class="text-gray-500 text-sm mb-7">Add a delivery address to speed up checkout.</p>
                    <button onclick="document.getElementById('addAddressModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-7 py-3 bg-[#8B1A1A] text-white text-sm font-semibold rounded-xl hover:bg-[#6B1414] transition-all shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                <select name="region_id" id="region_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent">
                    <option value="">-- Select Region --</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                <select name="province_id" id="province_id" required disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent bg-gray-100">
                    <option value="">-- Select Province --</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
                <select name="city_id" id="city_id" required disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent bg-gray-100">
                    <option value="">-- Select City/Municipality --</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                <select name="barangay_id" id="barangay_id" required disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent bg-gray-100">
                    <option value="">-- Select Barangay --</option>
                </select>
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
// Toast helper
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toastIcon');
    const msg = document.getElementById('toastMsg');
    msg.textContent = message;
    if (type === 'success') {
        icon.className = 'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-green-100';
        icon.innerHTML = '<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
    } else {
        icon.className = 'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-red-100';
        icon.innerHTML = '<svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
    }
    toast.classList.remove('hidden');
    toast.classList.add('animate-fade-in');
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => {
        toast.classList.add('hidden');
    }, 3500);
}

// AJAX Set as Default
function setDefault(addressId) {
    const btn = document.getElementById(`setDefaultBtn-${addressId}`);
    const form = document.getElementById(`setDefaultForm-${addressId}`);
    if (!form) return;

    // Visual loading state
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Setting...';

    const formData = new FormData(form);
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json, text/html, */*' }
    }).then(response => {
        if (response.ok || response.redirected) {
            // Update all cards visually without reload
            document.querySelectorAll('.address-card').forEach(card => {
                const cid = card.id.replace('address-card-', '');
                const isThis = (cid == addressId);

                // Border
                card.classList.toggle('border-[#8B1A1A]', isThis);
                card.classList.toggle('shadow-md', isThis);
                card.classList.toggle('border-gray-100', !isThis);
                card.classList.toggle('shadow-sm', !isThis);

                // Icon bg
                const icon = document.getElementById(`icon-${cid}`);
                const iconSvg = document.getElementById(`iconSvg-${cid}`);
                if (icon) {
                    icon.classList.toggle('bg-[#8B1A1A]', isThis);
                    icon.classList.toggle('bg-gray-100', !isThis);
                }
                if (iconSvg) {
                    iconSvg.classList.toggle('text-white', isThis);
                    iconSvg.classList.toggle('text-gray-400', !isThis);
                }

                // Badges
                const badges = document.getElementById(`badges-${cid}`);
                if (badges) {
                    // Remove existing default badge
                    badges.querySelectorAll('.default-badge').forEach(b => b.remove());
                    if (isThis) {
                        const badge = document.createElement('span');
                        badge.className = 'default-badge inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-[#8B1A1A] text-white';
                        badge.innerHTML = '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>Default';
                        badges.prepend(badge);
                    }
                }

                // Show/hide "Set as Default" footer
                const footer = card.querySelector('[id^="setDefaultBtn-"]')?.closest('div.mt-4');
                if (footer) {
                    footer.style.display = isThis ? 'none' : '';
                }
            });

            showToast('Default address updated!', 'success');
        } else {
            showToast('Failed to update default address.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Set as Default';
        }
    }).catch(() => {
        showToast('Something went wrong.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Set as Default';
    });
}

// Confirm delete with inline modal
function confirmDelete(addressId, name) {
    if (confirm(`Delete address for "${name}"? This cannot be undone.`)) {
        const form = document.getElementById(`delete-form-${addressId}`);
        if (form) form.submit();
    }
}

function editAddress(addressId) {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('auth_token') || sessionStorage.getItem('auth_token') || '';
    const fetchUrl = token ? `/addresses/${addressId}/edit?auth_token=${encodeURIComponent(token)}` : `/addresses/${addressId}/edit`;

    fetch(fetchUrl)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editFormContainer').innerHTML = html;

            // Inject auth_token into the dynamically loaded form's action URL and as hidden input
            if (token) {
                const form = document.querySelector('#editFormContainer form');
                if (form) {
                    try {
                        const actionUrl = new URL(form.action, window.location.origin);
                        if (!actionUrl.searchParams.has('auth_token')) {
                            actionUrl.searchParams.set('auth_token', token);
                            form.action = actionUrl.toString();
                        }
                    } catch(e) {}
                    if (!form.querySelector('input[name="auth_token"]')) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'auth_token';
                        input.value = token;
                        form.appendChild(input);
                    }
                }
            }

            document.getElementById('editAddressModal').classList.remove('hidden');
        })
        .catch(err => console.error('Error loading edit form:', err));
}

// Cascading dropdown logic
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const authToken = urlParams.get('auth_token') || sessionStorage.getItem('auth_token') || '';

    function apiUrl(path) {
        return authToken ? `${path}?auth_token=${encodeURIComponent(authToken)}` : path;
    }

    const regionSelect = document.getElementById('region_id');
    const provinceSelect = document.getElementById('province_id');
    const citySelect = document.getElementById('city_id');
    const barangaySelect = document.getElementById('barangay_id');
    
    // Load regions on page load
    loadRegions();
    
    // Region change event
    regionSelect.addEventListener('change', function() {
        const regionId = this.value;
        
        // Reset dependent dropdowns
        resetDropdown(provinceSelect, 'Select Province');
        resetDropdown(citySelect, 'Select City/Municipality');
        resetDropdown(barangaySelect, 'Select Barangay');
        
        if (regionId) {
            loadProvinces(regionId);
        }
    });
    
    // Province change event
    provinceSelect.addEventListener('change', function() {
        const provinceId = this.value;
        
        // Reset dependent dropdowns
        resetDropdown(citySelect, 'Select City/Municipality');
        resetDropdown(barangaySelect, 'Select Barangay');
        
        if (provinceId) {
            loadCities(provinceId);
        }
    });
    
    // City change event
    citySelect.addEventListener('change', function() {
        const cityId = this.value;
        
        // Reset dependent dropdown
        resetDropdown(barangaySelect, 'Select Barangay');
        
        if (cityId) {
            loadBarangays(cityId);
        }
    });
    
    // Load regions
    function loadRegions() {
        fetch(apiUrl('/addresses/api/regions'))
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
                }
            })
            .catch(error => console.error('Error loading regions:', error));
    }
    
    // Load provinces
    function loadProvinces(regionId) {
        provinceSelect.disabled = true;
        provinceSelect.classList.add('bg-gray-100');
        
        fetch(apiUrl(`/addresses/api/provinces/${regionId}`))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
                    data.data.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.id;
                        option.textContent = province.name;
                        provinceSelect.appendChild(option);
                    });
                    provinceSelect.disabled = false;
                    provinceSelect.classList.remove('bg-gray-100');
                }
            })
            .catch(error => console.error('Error loading provinces:', error));
    }
    
    // Load cities
    function loadCities(provinceId) {
        citySelect.disabled = true;
        citySelect.classList.add('bg-gray-100');
        
        fetch(apiUrl(`/addresses/api/cities/${provinceId}`))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    citySelect.innerHTML = '<option value="">-- Select City/Municipality --</option>';
                    data.data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id;
                        option.textContent = city.name;
                        citySelect.appendChild(option);
                    });
                    citySelect.disabled = false;
                    citySelect.classList.remove('bg-gray-100');
                }
            })
            .catch(error => console.error('Error loading cities:', error));
    }
    
    // Load barangays
    function loadBarangays(cityId) {
        barangaySelect.disabled = true;
        barangaySelect.classList.add('bg-gray-100');
        
        fetch(apiUrl(`/addresses/api/barangays/${cityId}`))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
                    data.data.forEach(barangay => {
                        const option = document.createElement('option');
                        option.value = barangay.id;
                        option.textContent = barangay.name;
                        barangaySelect.appendChild(option);
                    });
                    barangaySelect.disabled = false;
                    barangaySelect.classList.remove('bg-gray-100');
                }
            })
            .catch(error => console.error('Error loading barangays:', error));
    }
    
    // Reset dropdown helper
    function resetDropdown(selectElement, placeholder) {
        selectElement.innerHTML = `<option value="">-- ${placeholder} --</option>`;
        selectElement.disabled = true;
        selectElement.classList.add('bg-gray-100');
    }
});
</script>

@endsection
