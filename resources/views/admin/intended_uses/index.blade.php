@extends('layouts.admin')

@section('title', 'Intended Uses Management')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Intended Uses Management</h1>
        <p class="text-gray-600">Manage intended uses for custom orders</p>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
        <!-- Add New Intended Use Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Add New Intended Use</h2>
            
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
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Existing Intended Uses</h2>
            
            @if($intendedUses->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-500">No intended uses yet. Create one above!</p>
                </div>
            @else
                <!-- Edit/Toggle Section -->
                <div class="mt-8 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="background-color: #800000;">
                                <th class="px-6 py-3 text-left text-white font-semibold">Name</th>
                                <th class="px-6 py-3 text-center text-white font-semibold">Status</th>
                                <th class="px-6 py-3 text-center text-white font-semibold">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($intendedUses as $use)
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $use->name }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="w-5 h-5 toggle-active" data-id="{{ $use->id }}" data-route="{{ route('admin.intended_uses.toggle', $use->id) }}" {{ $use->is_active ? 'checked' : '' }} style="accent-color: #800000;">
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

<!-- Edit Modal -->
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

<script>
// Modal functions
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
    
    if (!name) {
        alert('Please enter a name');
        return;
    }
    
    // Send PATCH request to update
    fetch(`/admin/intended-uses/${id}`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: name
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the pill text
            const pills = document.querySelectorAll('[onclick*="openEditModal"]');
            pills.forEach(pill => {
                if (pill.getAttribute('onclick').includes(`'${id}'`)) {
                    const pillSpan = pill.closest('div');
                    const newPill = pillSpan.cloneNode(true);
                    newPill.querySelector('span').textContent = name;
                    newPill.querySelector('button').setAttribute('onclick', `openEditModal('${id}', '${name}')`);
                    pillSpan.replaceWith(newPill);
                }
            });
            
            // Update the table row
            const rows = document.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells[0] && cells[0].textContent.includes(document.getElementById('editName').value)) {
                    cells[0].textContent = name;
                }
            });
            
            closeEditModal();
            alert('Intended use updated successfully!');
            location.reload(); // Reload to ensure everything is in sync
        } else {
            alert('Failed to update: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update intended use. Please try again.');
    });
}

// Close modal when clicking outside
document.getElementById('editModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

document.querySelectorAll('.toggle-active').forEach(checkbox => {
    checkbox.addEventListener('change', async function() {
        const route = this.dataset.route;
        try {
            const response = await fetch(route, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                const statusSpan = this.parentElement.querySelector('span');
                statusSpan.textContent = data.is_active ? 'Active' : 'Inactive';
                statusSpan.style.color = data.is_active ? '#800000' : '#999999';
            }
        } catch (error) {
            console.error('Error:', error);
            this.checked = !this.checked;
        }
    });
});

// Delete intended use from pills
function deleteIntendedUse(id, route, name) {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) {
        return;
    }
    
    fetch(route, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Find and remove the pill
            const pills = document.querySelectorAll('[onclick*="deleteIntendedUse"]');
            pills.forEach(pill => {
                if (pill.getAttribute('onclick').includes(`'${id}'`)) {
                    pill.closest('div').style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => {
                        pill.closest('div').remove();
                        
                        // Reload page to refresh table
                        setTimeout(() => {
                            location.reload();
                        }, 300);
                    }, 300);
                }
            });
        } else {
            alert('Failed to delete: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete intended use. Please try again.');
    });
}
</script>
@endsection
