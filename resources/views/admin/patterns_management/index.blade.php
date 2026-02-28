@extends('layouts.admin')

@section('title', 'Patterns Management')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl md:text-4xl font-bold text-gray-900 mb-2">Patterns Management</h1>
        <p class="text-gray-600">Manage fabric types and intended uses for custom orders</p>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- FABRIC TYPES SECTION -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 mb-8">
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h2 id="fabric-types" class="text-xl md:text-3xl font-bold text-gray-900 mb-2">Fabric Types</h2>
                    <p class="text-gray-600">Manage fabric types available for custom orders</p>
                </div>
                <button type="button" onclick="openAddFabricTypeModal()" class="px-6 py-3 rounded-lg font-bold text-white transition-all" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                    + Add Fabric Type
                </button>
            </div>
        </div>

        @if($fabricTypes->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead style="background-color: #800000;">
                        <tr>
                            <th class="px-6 py-3 text-left text-white font-semibold">Name</th>
                            <th class="px-6 py-3 text-left text-white font-semibold">Description</th>
                            <th class="px-6 py-3 text-center text-white font-semibold">Status</th>
                            <th class="px-6 py-3 text-center text-white font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fabricTypes as $fabricType)
                            <tr class="border-t border-gray-200 hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $fabricType->name }}</td>
                                <td class="px-6 py-4 text-gray-600 text-sm">{{ Str::limit($fabricType->description ?? '—', 50) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="w-5 h-5 toggle-active" data-id="{{ $fabricType->id }}" data-route="{{ route('admin.fabric_types.toggle', $fabricType->id) }}" {{ $fabricType->is_active ? 'checked' : '' }} style="accent-color: #800000;">
                                        <span class="ml-2 text-sm font-medium" style="color: {{ $fabricType->is_active ? '#800000' : '#999999' }};">
                                            {{ $fabricType->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </label>
                                </td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <button type="button" onclick="openEditFabricTypeModal('{{ $fabricType->id }}', '{{ $fabricType->name }}', '{{ $fabricType->description }}')" class="inline-block px-3 py-1 rounded text-white text-sm font-semibold transition-all" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                                        Edit
                                    </button>
                                    <button type="button" class="px-3 py-1 rounded bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-all delete-btn-fabric" data-id="{{ $fabricType->id }}" data-route="{{ route('admin.fabric_types.destroy', $fabricType->id) }}">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center">
                <p class="text-gray-500 mb-4">No fabric types found</p>
                <button type="button" onclick="openAddFabricTypeModal()" class="inline-block px-6 py-2 rounded-lg font-semibold text-white" style="background-color: #800000;">
                    Create First Fabric Type
                </button>
            </div>
        @endif
    </div>

    <!-- INTENDED USES SECTION -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
        <!-- Add New Intended Use Section -->
        <div class="mb-8">
            <h2 id="intended-uses" class="text-xl md:text-3xl font-bold text-gray-900 mb-2">Intended Uses</h2>
            <p class="text-gray-600 mb-6">Manage intended uses for custom orders</p>
            
            <form id="newIntendedUseForm" action="{{ route('admin.intended_uses.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" name="name" id="newName" placeholder="e.g., Clothing, Home Decor"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-700"
                            style="border-color: #800000;">
                        @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-2 text-white rounded-lg transition-colors font-medium flex-1" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg> Add Intended Use
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <hr class="my-8">

        <!-- Existing Intended Uses Section -->
        <div>
            @if($intendedUses->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-500">No intended uses yet. Create one above!</p>
                </div>
            @else
                <!-- Management Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="background-color: #800000;">
                                <th class="px-6 py-3 text-left text-white font-semibold">Name</th>
                                <th class="px-6 py-3 text-center text-white font-semibold">Status</th>
                                <th class="px-6 py-3 text-center text-white font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($intendedUses as $use)
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $use->name }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="w-5 h-5 toggle-active-intended" data-id="{{ $use->id }}" data-route="{{ route('admin.intended_uses.toggle', $use->id) }}" {{ $use->is_active ? 'checked' : '' }} style="accent-color: #800000;">
                                            <span class="ml-2 text-sm font-medium" style="color: {{ $use->is_active ? '#800000' : '#999999' }};">
                                                {{ $use->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </label>
                                    </td>
                                    <td class="px-6 py-4 text-center flex gap-2 justify-center">
                                        <button type="button" onclick="openEditModal('{{ $use->id }}', '{{ $use->name }}')" class="px-3 py-1 rounded text-white text-sm font-semibold transition-all" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                                            Edit
                                        </button>
                                        <button type="button" onclick="deleteIntendedUse('{{ $use->id }}', '{{ route('admin.intended_uses.destroy', $use->id) }}', '{{ $use->name }}')" class="px-3 py-1 rounded text-white text-sm font-semibold transition-all bg-red-600 hover:bg-red-700">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Fabric Type Modal -->
<div id="addFabricTypeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Add Fabric Type</h2>
        
        <form id="addFabricTypeForm" onsubmit="submitAddFabricTypeForm(event)">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input type="text" id="addFabricTypeName" name="name" placeholder="e.g., Cotton, Silk"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2"
                        style="border-color: #800000;" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="addFabricTypeDescription" name="description" placeholder="Enter description"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2"
                        style="border-color: #800000;" rows="3"></textarea>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 px-4 py-2 text-white rounded-lg transition-colors font-medium" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                    Add
                </button>
                <button type="button" onclick="closeAddFabricTypeModal()" class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Fabric Type Modal -->
<div id="editFabricTypeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Fabric Type</h2>
        
        <form id="editFabricTypeForm" onsubmit="submitEditFabricTypeForm(event)">
            <input type="hidden" id="editFabricTypeId">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input type="text" id="editFabricTypeName" placeholder="Enter name"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2"
                        style="border-color: #800000;" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="editFabricTypeDescription" placeholder="Enter description"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2"
                        style="border-color: #800000;" rows="3"></textarea>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 px-4 py-2 text-white rounded-lg transition-colors font-medium" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                    Save
                </button>
                <button type="button" onclick="closeEditFabricTypeModal()" class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal for Intended Uses -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Intended Use</h2>
        
        <form id="editForm" onsubmit="submitEditForm(event)">
            <input type="hidden" id="editId">
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                <input type="text" id="editName" placeholder="Enter name"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2"
                    style="border-color: #800000;">
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2 text-white rounded-lg transition-colors font-medium" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                    Save
                </button>
                <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notification -->
<div id="pmToastContainer" class="fixed top-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none"></div>

<script>
// ============ AUTH HELPERS ============
function getAuthToken() {
    const p = new URLSearchParams(window.location.search);
    return p.get('auth_token') || sessionStorage.getItem('auth_token') || '';
}
function adminUrl(path) {
    const t = getAuthToken();
    if (!t) return path;
    return path + (path.includes('?') ? '&' : '?') + 'auth_token=' + encodeURIComponent(t);
}

// ============ TOAST HELPER ============
function showToast(message, type = 'success') {
    const container = document.getElementById('pmToastContainer');
    const toast = document.createElement('div');
    const bg = type === 'success' ? 'bg-green-600' : 'bg-red-600';
    const icon = type === 'success'
        ? '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
        : '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
    toast.className = `pointer-events-auto flex items-center gap-3 px-5 py-3 rounded-xl shadow-xl text-white text-sm font-medium ${bg} translate-x-16 opacity-0 transition-all duration-300`;
    toast.innerHTML = icon + `<span>${message}</span>`;
    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-16', 'opacity-0');
    });
    setTimeout(() => {
        toast.classList.add('translate-x-16', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// ============ FABRIC TYPES SCRIPTS ============

function openAddFabricTypeModal() {
    document.getElementById('addFabricTypeForm').reset();
    document.getElementById('addFabricTypeModal').classList.remove('hidden');
    document.getElementById('addFabricTypeName').focus();
}

function closeAddFabricTypeModal() {
    document.getElementById('addFabricTypeModal').classList.add('hidden');
    document.getElementById('addFabricTypeForm').reset();
}

function openEditFabricTypeModal(id, name, description) {
    document.getElementById('editFabricTypeId').value = id;
    document.getElementById('editFabricTypeName').value = name;
    document.getElementById('editFabricTypeDescription').value = description || '';
    document.getElementById('editFabricTypeModal').classList.remove('hidden');
    document.getElementById('editFabricTypeName').focus();
}

function closeEditFabricTypeModal() {
    document.getElementById('editFabricTypeModal').classList.add('hidden');
    document.getElementById('editFabricTypeForm').reset();
}

function submitAddFabricTypeForm(event) {
    event.preventDefault();

    const name = document.getElementById('addFabricTypeName').value.trim();
    const description = document.getElementById('addFabricTypeDescription').value.trim();
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const submitBtn = event.submitter || event.target.querySelector('[type="submit"]');

    if (!name) {
        showToast('Please enter a fabric type name.', 'error');
        return;
    }

    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Adding…'; }

    fetch(adminUrl('/admin/fabric-types'), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, description, base_price_per_meter: 0, is_active: true })
    })
    .then(async response => {
        if (!response.ok) {
            const text = await response.text();
            throw new Error(`Server error ${response.status}: ${text.slice(0, 200)}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success || data.id) {
            closeAddFabricTypeModal();
            showToast('Fabric type added successfully!');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Failed to add: ' + (data.message || 'Unknown error'), 'error');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Add'; }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to add fabric type. Please try again.', 'error');
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Add'; }
    });
}

function submitEditFabricTypeForm(event) {
    event.preventDefault();

    const id = document.getElementById('editFabricTypeId').value;
    const name = document.getElementById('editFabricTypeName').value.trim();
    const description = document.getElementById('editFabricTypeDescription').value.trim();
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const submitBtn = event.submitter || event.target.querySelector('[type="submit"]');

    if (!name) {
        showToast('Please enter a fabric type name.', 'error');
        return;
    }

    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving…'; }

    fetch(adminUrl(`/admin/fabric-types/${id}`), {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, description })
    })
    .then(async response => {
        if (!response.ok) {
            const text = await response.text();
            throw new Error(`Server error ${response.status}: ${text.slice(0, 200)}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeEditFabricTypeModal();
            showToast('Fabric type updated successfully!');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Failed to update: ' + (data.message || 'Unknown error'), 'error');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Save'; }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update fabric type. Please try again.', 'error');
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Save'; }
    });
}

// Close modals on backdrop click
document.getElementById('addFabricTypeModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddFabricTypeModal();
});
document.getElementById('editFabricTypeModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditFabricTypeModal();
});

// Toggle active — fabric types
document.querySelectorAll('.toggle-active').forEach(checkbox => {
    checkbox.addEventListener('change', async function() {
        const route = adminUrl(this.dataset.route);
        const originalChecked = !this.checked;
        try {
            const response = await fetch(route, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            if (!response.ok) throw new Error('Server error ' + response.status);
            const data = await response.json();
            if (data.success) {
                const statusSpan = this.parentElement.querySelector('span');
                statusSpan.textContent = data.is_active ? 'Active' : 'Inactive';
                statusSpan.style.color = data.is_active ? '#800000' : '#999999';
                showToast(data.message || (data.is_active ? 'Activated.' : 'Deactivated.'));
            } else {
                this.checked = originalChecked;
                showToast(data.message || 'Failed to toggle status.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.checked = originalChecked;
            showToast('Failed to toggle status. Please try again.', 'error');
        }
    });
});

// Delete — fabric types
document.querySelectorAll('.delete-btn-fabric').forEach(button => {
    button.addEventListener('click', async function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this fabric type?')) return;

        const route = adminUrl(this.dataset.route);
        const row = this.closest('tr');

        try {
            const response = await fetch(route, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            if (!response.ok) throw new Error('Server error ' + response.status);
            const data = await response.json();

            if (data.success) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
                showToast(data.message || 'Fabric type deleted.');
            } else {
                showToast('Failed to delete: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to delete fabric type. Please try again.', 'error');
        }
    });
});

// ============ INTENDED USES SCRIPTS ============
function openEditModal(id, name) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editName').focus();
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editForm').reset();
}

function submitEditForm(event) {
    event.preventDefault();

    const id = document.getElementById('editId').value;
    const name = document.getElementById('editName').value.trim();
    const submitBtn = event.submitter || event.target.querySelector('[type="submit"]');

    if (!name) {
        showToast('Please enter a name.', 'error');
        return;
    }

    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving…'; }

    fetch(adminUrl(`/admin/intended-uses/${id}`), {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name })
    })
    .then(async response => {
        if (!response.ok) throw new Error('Server error ' + response.status);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeEditModal();
            showToast('Intended use updated successfully!');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Failed to update: ' + (data.message || 'Unknown error'), 'error');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Save'; }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update intended use. Please try again.', 'error');
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Save'; }
    });
}

document.getElementById('editModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// Toggle active — intended uses
document.querySelectorAll('.toggle-active-intended').forEach(checkbox => {
    checkbox.addEventListener('change', async function() {
        const route = adminUrl(this.dataset.route);
        const originalChecked = !this.checked;
        try {
            const response = await fetch(route, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            if (!response.ok) throw new Error('Server error ' + response.status);
            const data = await response.json();
            if (data.success) {
                const statusSpan = this.parentElement.querySelector('span');
                statusSpan.textContent = data.is_active ? 'Active' : 'Inactive';
                statusSpan.style.color = data.is_active ? '#800000' : '#999999';
                showToast(data.message || (data.is_active ? 'Activated.' : 'Deactivated.'));
            } else {
                this.checked = originalChecked;
                showToast(data.message || 'Failed to toggle status.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.checked = originalChecked;
            showToast('Failed to toggle status. Please try again.', 'error');
        }
    });
});

function deleteIntendedUse(id, route, name) {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) return;

    fetch(adminUrl(route), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        if (!response.ok) throw new Error('Server error ' + response.status);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Intended use deleted.');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Failed to delete: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to delete intended use. Please try again.', 'error');
    });
}
</script>
@endsection
