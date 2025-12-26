@extends('layouts.admin')

@section('title', 'Cultural Heritage Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-[#800000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">Cultural Heritage Management</h1>
                <p class="text-white text-lg">Manage Yakan history, traditions, and cultural content</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('admin.cultural-heritage.create') }}" class="bg-white text-[#800000] px-6 py-3 rounded-lg hover:bg-gray-100 font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add New Content
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#800000] hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Total Content</p>
                    <p class="text-3xl font-bold text-[#800000] mt-2">{{ $heritages->total() }}</p>
                </div>
                <div class="w-14 h-14 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#800000] hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Published</p>
                    <p class="text-3xl font-bold text-[#800000] mt-2">{{ $heritages->where('is_published', true)->count() }}</p>
                </div>
                <div class="w-14 h-14 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#800000] hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Drafts</p>
                    <p class="text-3xl font-bold text-[#800000] mt-2">{{ $heritages->where('is_published', false)->count() }}</p>
                </div>
                <div class="w-14 h-14 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-[#800000] hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Categories</p>
                    <p class="text-3xl font-bold text-[#800000] mt-2">{{ $heritages->pluck('category')->unique()->count() }}</p>
                </div>
                <div class="w-14 h-14 bg-[#800000] rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-[#800000]">
        <form method="GET" class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search content..." 
                           class="pl-10 pr-4 py-2 border-2 border-[#800000] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                    <svg class="w-5 h-5 absolute left-3 top-3 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <select name="category" class="px-4 py-2 border-2 border-[#800000] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]">
                    <option value="">All Categories</option>
                    <option value="history" {{ request('category') == 'history' ? 'selected' : '' }}>History</option>
                    <option value="tradition" {{ request('category') == 'tradition' ? 'selected' : '' }}>Tradition</option>
                    <option value="culture" {{ request('category') == 'culture' ? 'selected' : '' }}>Culture</option>
                    <option value="art" {{ request('category') == 'art' ? 'selected' : '' }}>Art</option>
                    <option value="crafts" {{ request('category') == 'crafts' ? 'selected' : '' }}>Crafts</option>
                    <option value="language" {{ request('category') == 'language' ? 'selected' : '' }}>Language</option>
                </select>
                <select name="status" class="px-4 py-2 border-2 border-[#800000] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]">
                    <option value="">All Status</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="px-6 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-all duration-300 font-bold shadow-md hover:shadow-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Search
                </button>
                <a href="{{ route('admin.cultural-heritage.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-bold flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Content List -->
    <div class="bg-white rounded-xl shadow-lg border-2 border-[#800000] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#800000]">
                <thead class="bg-[#800000]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Content</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Order</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#800000]">
                    @forelse($heritages as $heritage)
                    <tr class="hover:bg-gray-50 transition-colors" data-heritage-id="{{ $heritage->id }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($heritage->image)
                                <img src="{{ $heritage->image_url }}" 
                                     alt="{{ $heritage->title }}" 
                                     class="w-16 h-16 rounded-lg object-cover mr-4 border-2 border-[#800000]"
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mr-4 border-2 border-[#800000]\'><svg class=\'w-8 h-8 text-[#800000]\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg></div><div><div class=\'text-sm font-bold text-gray-900\'>{{ $heritage->title }}</div><div class=\'text-sm text-gray-600\'>{{ Str::limit($heritage->summary ?? $heritage->excerpt, 60) }}</div></div>';">
                                @else
                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mr-4 border-2 border-[#800000]">
                                    <svg class="w-8 h-8 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                @endif
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ $heritage->title }}</div>
                                    <div class="text-sm text-gray-600">{{ Str::limit($heritage->summary ?? $heritage->excerpt, 60) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-white text-[#800000] border-2 border-[#800000]">
                                {{ ucfirst($heritage->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button onclick="toggleStatus({{ $heritage->id }})" class="px-3 py-1 text-xs font-bold rounded-full {{ $heritage->is_published ? 'bg-[#800000] text-white border-2 border-[#800000]' : 'bg-gray-200 text-gray-700 border-2 border-gray-300' }}">
                                {{ $heritage->is_published ? 'Published' : 'Draft' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">
                            {{ $heritage->order }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">
                            {{ $heritage->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold">
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('admin.cultural-heritage.edit', $heritage->id) }}" class="text-[#800000] hover:text-[#600000] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button onclick="confirmDelete({{ $heritage->id }}, '{{ addslashes($heritage->title) }}')" class="text-red-600 hover:text-red-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="w-20 h-20 text-[#800000] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"/></svg>
                            <p class="text-gray-600 text-lg font-semibold">No cultural heritage content found</p>
                            <a href="{{ route('admin.cultural-heritage.create') }}" class="mt-4 inline-flex items-center px-6 py-3 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-all duration-300 font-bold shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add Your First Content
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($heritages->hasPages())
        <div class="px-6 py-4 border-t-2 border-[#800000]">
            {{ $heritages->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-2h2m-2 0h-2m6-4h.01M9 16h.01M15 16h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Delete Content</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <strong id="heritageName" class="text-gray-900"></strong>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="deleteHeritage()" id="confirmDeleteBtn" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed top-4 right-4 z-50 transform transition-all duration-300">
    <div class="bg-white rounded-lg shadow-xl border-l-4 p-4 max-w-md">
        <div class="flex items-center">
            <div id="toastIcon" class="flex-shrink-0"></div>
            <div class="ml-3">
                <p id="toastMessage" class="text-sm font-medium"></p>
            </div>
            <button onclick="closeToast()" class="ml-auto flex-shrink-0">
                <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let heritageToDelete = null;

function confirmDelete(heritageId, heritageName) {
    heritageToDelete = heritageId;
    document.getElementById('heritageName').textContent = heritageName;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    heritageToDelete = null;
}

function deleteHeritage() {
    if (!heritageToDelete) return;
    
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const originalText = deleteBtn.innerHTML;
    
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
    
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/admin/cultural-heritage/${heritageToDelete}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        closeDeleteModal();
        
        if (data.success) {
            showToast(data.message, 'success');
            
            const row = document.querySelector(`[data-heritage-id="${heritageToDelete}"]`);
            if (row) {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.remove();
                    setTimeout(() => window.location.reload(), 1000);
                }, 300);
            } else {
                setTimeout(() => window.location.reload(), 1500);
            }
        } else {
            showToast(data.message || 'Failed to delete content', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        closeDeleteModal();
        showToast('An error occurred while deleting', 'error');
    })
    .finally(() => {
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
    });
}

function toggleStatus(heritageId) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/admin/cultural-heritage/${heritageId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update status', 'error');
    });
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    const toastContainer = toast.querySelector('div');
    
    toastMessage.textContent = message;
    
    if (type === 'success') {
        toastIcon.innerHTML = '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        toastContainer.classList.remove('border-red-500');
        toastContainer.classList.add('border-green-500');
    } else {
        toastIcon.innerHTML = '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        toastContainer.classList.remove('border-green-500');
        toastContainer.classList.add('border-red-500');
    }
    
    toast.classList.remove('hidden');
    setTimeout(() => closeToast(), 3000);
}

function closeToast() {
    document.getElementById('toast').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});

document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
@endpush
