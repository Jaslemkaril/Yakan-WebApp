@extends('layouts.admin')

@section('title', 'Chat Management')

@section('content')
<style>
    .admin-header { background: linear-gradient(135deg, #800000 0%, #600000 100%); color: #ffffff; }
    .stat-card { background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%); border: 1px solid #e5e7eb; }
    .stat-card h3 { color: #800000; }
    .stat-card .value { color: #800000; font-size: 2rem; font-weight: bold; }
    .filter-section { background: linear-gradient(135deg, #800000 0%, #600000 100%); color: #ffffff; padding: 20px; border-radius: 8px; margin-bottom: 24px; }
    .filter-section h3 { color: #ffffff; }
    .filter-section label { color: #ffffff; }
    .filter-section input, .filter-section select { background: rgba(255, 255, 255, 0.1); border: 1px solid #600000; color: #ffffff; }
    .filter-section input::placeholder { color: #e8d4d4; }
    .table-card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
    .table-card table { width: 100%; border-collapse: collapse; }
    .table-card thead { background: linear-gradient(135deg, #800000 0%, #600000 100%); color: #ffffff; }
    .table-card th { padding: 12px; text-align: left; font-weight: 600; }
    .table-card td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
    .table-card tbody tr:hover { background: #f8f9fa; }
    .badge-open { background: #d4edda; color: #155724; }
    .badge-pending { background: #fff3cd; color: #856404; }
    .badge-closed { background: #e2e3e5; color: #383d41; }
    .btn-view { background: linear-gradient(135deg, #800000 0%, #600000 100%); color: #ffffff; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.875rem; display: inline-block; }
    .btn-view:hover { background: linear-gradient(135deg, #600000 0%, #400000 100%); }
    .btn-delete { background: #dc3545; color: #ffffff; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 0.875rem; }
    .btn-delete:hover { background: #c82333; }
    .empty-state { text-align: center; padding: 48px 20px; }
    .empty-state i { font-size: 3rem; color: #800000; margin-bottom: 16px; }
</style>

<div class="admin-header p-6 rounded-lg mb-8">
    <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 4px;">
        <i class="fas fa-comments mr-3"></i>Chat Management
    </h1>
    <p style="color: #e8d4d4;">Manage customer support chats</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-4 gap-6 mb-8">
    <div class="stat-card p-6 rounded-lg shadow">
        <h3 class="text-sm font-semibold mb-2">TOTAL CHATS</h3>
        <div class="value">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card p-6 rounded-lg shadow">
        <h3 class="text-sm font-semibold mb-2">OPEN CHATS</h3>
        <div class="value" style="color: #28a745;">{{ $stats['open'] }}</div>
    </div>
    <div class="stat-card p-6 rounded-lg shadow">
        <h3 class="text-sm font-semibold mb-2">PENDING CHATS</h3>
        <div class="value" style="color: #ffc107;">{{ $stats['pending'] }}</div>
    </div>
    <div class="stat-card p-6 rounded-lg shadow">
        <h3 class="text-sm font-semibold mb-2">CLOSED CHATS</h3>
        <div class="value" style="color: #800000;">{{ $stats['closed'] }}</div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 16px;">
        <i class="fas fa-filter mr-2"></i>Filters
    </h3>
    <form method="GET" class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold mb-2">Status</label>
            <select name="status" class="w-full px-4 py-2 rounded-lg" style="background: rgba(255, 255, 255, 0.1); border: 1px solid #600000; color: #ffffff;">
                <option value="all" style="color: #333;">All Statuses</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }} style="color: #333;">Open</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }} style="color: #333;">Pending</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }} style="color: #333;">Closed</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2">Search</label>
            <input type="text" name="search" placeholder="Search by name, email, or subject..." class="w-full px-4 py-2 rounded-lg" value="{{ request('search') }}" style="background: rgba(255, 255, 255, 0.1); border: 1px solid #600000; color: #ffffff;">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 px-4 py-2 rounded-lg font-semibold transition" style="background: linear-gradient(135deg, #ffffff 0%, #f3f3f3 100%); color: #800000;">
                <i class="fas fa-search mr-2"></i> Search
            </button>
            <a href="{{ route('admin.chats.index') }}" class="flex-1 px-4 py-2 rounded-lg font-semibold transition text-center" style="background: rgba(255, 255, 255, 0.1); border: 1px solid #600000; color: #ffffff;">
                <i class="fas fa-redo mr-2"></i> Reset
            </a>
        </div>
    </form>
</div>

<!-- Chats Table -->
<div class="table-card shadow">
    @if($chats->count() > 0)
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>SUBJECT</th>
                        <th>CUSTOMER</th>
                        <th>EMAIL</th>
                        <th>STATUS</th>
                        <th>MESSAGES</th>
                        <th>LAST UPDATED</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chats as $chat)
                        <tr>
                            <td>
                                <strong style="color: #800000;">{{ Str::limit($chat->subject, 30) }}</strong>
                            </td>
                            <td style="color: #333;">{{ $chat->user_name ?? 'Guest' }}</td>
                            <td style="color: #666;">{{ $chat->user_email ?? 'N/A' }}</td>
                            <td>
                                <span class="badge-{{ $chat->status }} px-3 py-1 rounded-full text-xs font-semibold">
                                    {{ ucfirst($chat->status) }}
                                </span>
                                @if($chat->unreadCount() > 0)
                                    <span class="badge-pending px-3 py-1 rounded-full text-xs font-semibold ml-2">{{ $chat->unreadCount() }} unread</span>
                                @endif
                            </td>
                            <td style="color: #333;">{{ $chat->messages()->count() }}</td>
                            <td style="color: #666;">{{ $chat->updated_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('admin.chats.show', $chat) }}" class="btn-view">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                <form action="{{ route('admin.chats.destroy', $chat) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this chat?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 p-4">
            {{ $chats->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3 style="font-size: 1.25rem; font-weight: 600; color: #333; margin-bottom: 8px;">No chats found</h3>
            <p style="color: #666;">There are no chats matching your filters</p>
        </div>
    @endif
</div>
@endsection
