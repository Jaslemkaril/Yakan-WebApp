@extends('layouts.admin')

@section('title', 'Fabric Types Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Fabric Types</h1>
                <p class="text-gray-600">Manage fabric types available for custom orders</p>
            </div>
            <a href="{{ route('admin.fabric_types.create') }}" class="px-6 py-3 rounded-lg font-bold text-white transition-all" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                + Add Fabric Type
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Fabric Types Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        @if($fabricTypes->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                <thead style="background-color: #800000;">
                    <tr>
                        <th class="px-6 py-3 text-left text-white font-semibold">Name</th>
                        <th class="px-6 py-3 text-left text-white font-semibold">Icon</th>
                        <th class="px-6 py-3 text-left text-white font-semibold">Description</th>
                        <th class="px-6 py-3 text-center text-white font-semibold">Status</th>
                        <th class="px-6 py-3 text-center text-white font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fabricTypes as $fabricType)
                        <tr class="border-t border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $fabricType->name }}</td>
                            <td class="px-6 py-4">
                                @if($fabricType->icon)
                                    <span class="text-2xl">{{ $fabricType->icon }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-sm">{{ Str::limit($fabricType->description ?? '—', 50) }}</td>
                            <td class="px-6 py-4 text-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="w-5 h-5 toggle-active" data-id="{{ $fabricType->id }}" data-route="{{ route('admin.fabric_types.toggle', $fabricType->id) }}" {{ $fabricType->is_active ? 'checked' : '' }} style="accent-color: #800000;">
                                    <span class="ml-2 text-sm font-medium {{ $fabricType->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                        {{ $fabricType->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </label>
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <a href="{{ route('admin.fabric_types.edit', $fabricType->id) }}" class="inline-block px-3 py-1 rounded text-white text-sm font-semibold transition-all" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                                    Edit
                                </a>
                                <button type="button" class="px-3 py-1 rounded bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-all delete-btn" data-id="{{ $fabricType->id }}" data-route="{{ route('admin.fabric_types.destroy', $fabricType->id) }}">
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
                <a href="{{ route('admin.fabric_types.create') }}" class="inline-block px-6 py-2 rounded-lg font-semibold text-white" style="background-color: #800000;">
                    Create First Fabric Type
                </a>
            </div>
        @endif
    </div>
</div>

<script>
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
                // Update the status text
                const statusSpan = this.parentElement.querySelector('span');
                statusSpan.textContent = data.is_active ? 'Active' : 'Inactive';
                statusSpan.className = 'ml-2 text-sm font-medium ' + (data.is_active ? 'text-green-600' : 'text-gray-400');
            }
        } catch (error) {
            console.error('Error:', error);
            this.checked = !this.checked;
        }
    });
});

document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', async function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete this fabric type?')) {
            return;
        }
        
        const route = this.dataset.route;
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
            
            const data = await response.json();
            
            if (data.success) {
                // Remove the row from the table
                row.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => {
                    row.remove();
                }, 300);
                
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.className = 'mb-6 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700';
                successDiv.textContent = data.message || 'Fabric type deleted successfully.';
                
                const container = document.querySelector('.container');
                const firstElement = container.querySelector('.mb-8');
                firstElement.insertAdjacentElement('afterend', successDiv);
                
                // Auto-remove success message after 3 seconds
                setTimeout(() => {
                    successDiv.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => {
                        successDiv.remove();
                    }, 300);
                }, 3000);
            } else {
                alert('Failed to delete fabric type: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to delete fabric type. Please try again.');
        }
    });
});
</script>
@endsection
