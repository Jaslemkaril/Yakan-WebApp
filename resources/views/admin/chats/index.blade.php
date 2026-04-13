@extends('layouts.admin')

@section('title', 'Chat Management')

@section('content')
<style>
    .chat-index-wrap { background: #f8f7f5; min-height: 100vh; margin: -24px; padding: 28px; }

    /* Page header */
    .ci-page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; }
    .ci-page-header h1 { font-size: 1.6rem; font-weight: 700; color: #1a1a1a; display: flex; align-items: center; gap: 10px; }
    .ci-page-header h1 .icon-wrap { width: 40px; height: 40px; background: linear-gradient(135deg, #800000, #5a0000); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; }
    .ci-page-header p { color: #6b7280; font-size: 0.875rem; margin-top: 3px; }

    /* Stat cards */
    .ci-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .ci-stat { background: #fff; border-radius: 14px; padding: 20px 20px 16px; border: 1px solid #e9e5e0; box-shadow: 0 1px 4px rgba(0,0,0,0.04); display: flex; align-items: flex-start; gap: 16px; }
    .ci-stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .ci-stat-icon.total  { background: #fdf2f2; color: #800000; }
    .ci-stat-icon.open   { background: #ecfdf5; color: #059669; }
    .ci-stat-icon.pending{ background: #fffbeb; color: #d97706; }
    .ci-stat-icon.closed { background: #f3f4f6; color: #6b7280; }
    .ci-stat-body {}
    .ci-stat-body .label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #9ca3af; margin-bottom: 4px; }
    .ci-stat-body .value { font-size: 1.9rem; font-weight: 800; line-height: 1; }
    .ci-stat-body .value.total  { color: #800000; }
    .ci-stat-body .value.open   { color: #059669; }
    .ci-stat-body .value.pending{ color: #d97706; }
    .ci-stat-body .value.closed { color: #6b7280; }

    /* Filter card */
    .ci-filter { background: #fff; border-radius: 14px; padding: 18px 20px; border: 1px solid #e9e5e0; box-shadow: 0 1px 4px rgba(0,0,0,0.04); margin-bottom: 20px; }
    .ci-filter-row { display: flex; gap: 12px; align-items: flex-end; }
    .ci-filter-row .f-group { flex: 1; }
    .ci-filter-row .f-group label { display: block; font-size: 0.75rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px; }
    .ci-filter-row .f-group select,
    .ci-filter-row .f-group input { width: 100%; padding: 9px 14px; border: 1.5px solid #e5e7eb; border-radius: 9px; font-size: 0.875rem; color: #1f2937; background: #fafafa; transition: border-color 0.2s; outline: none; }
    .ci-filter-row .f-group select:focus,
    .ci-filter-row .f-group input:focus { border-color: #800000; background: #fff; }
    .ci-filter-row .f-actions { display: flex; gap: 8px; }
    .btn-filter-search { padding: 9px 20px; background: linear-gradient(135deg, #800000, #5a0000); color: #fff; border: none; border-radius: 9px; font-size: 0.875rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: opacity 0.2s; white-space: nowrap; }
    .btn-filter-search:hover { opacity: 0.9; }
    .btn-filter-reset { padding: 9px 16px; background: #f3f4f6; color: #374151; border: 1.5px solid #e5e7eb; border-radius: 9px; font-size: 0.875rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 6px; white-space: nowrap; transition: background 0.2s; }
    .btn-filter-reset:hover { background: #e9e5e0; }

    /* Table card */
    .ci-table-card { background: #fff; border-radius: 14px; border: 1px solid #e9e5e0; box-shadow: 0 1px 4px rgba(0,0,0,0.04); overflow: hidden; }
    .ci-table-card table { width: 100%; border-collapse: collapse; }
    .ci-table-card thead tr { background: #fdf8f8; border-bottom: 2px solid #e9e0e0; }
    .ci-table-card th { padding: 11px 16px; text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #800000; }
    .ci-table-card td { padding: 14px 16px; border-bottom: 1px solid #f3f1ee; vertical-align: middle; }
    .ci-table-card tbody tr:last-child td { border-bottom: none; }
    .ci-table-card tbody tr:hover { background: #fdf9f9; }

    /* Customer avatar */
    .ci-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #800000, #5a0000); color: #fff; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

    /* Subject */
    .ci-subject { font-weight: 600; color: #1a1a1a; font-size: 0.875rem; }
    .ci-subject-sub { font-size: 0.75rem; color: #9ca3af; margin-top: 2px; }

    /* Status badges */
    .ci-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; }
    .ci-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .ci-badge.open    { background: #ecfdf5; color: #065f46; }
    .ci-badge.open::before    { background: #10b981; }
    .ci-badge.pending { background: #fffbeb; color: #92400e; }
    .ci-badge.pending::before { background: #f59e0b; }
    .ci-badge.closed  { background: #f3f4f6; color: #374151; }
    .ci-badge.closed::before  { background: #9ca3af; }
    .ci-unread { display: inline-flex; align-items: center; gap: 4px; background: #fef2f2; color: #991b1b; padding: 2px 8px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; margin-left: 6px; }

    /* Message count chip */
    .ci-msg-count { display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 22px; background: #f3f4f6; color: #374151; border-radius: 20px; font-size: 0.75rem; font-weight: 600; padding: 0 8px; }

    /* Action buttons */
    .ci-btn-view { display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; background: linear-gradient(135deg, #800000, #5a0000); color: #fff; border-radius: 8px; font-size: 0.8rem; font-weight: 600; text-decoration: none; transition: opacity 0.2s, transform 0.1s; }
    .ci-btn-view:hover { opacity: 0.88; transform: translateY(-1px); }
    .ci-btn-del { display: inline-flex; align-items: center; gap: 5px; padding: 6px 10px; background: #fff0f0; color: #dc2626; border: 1.5px solid #fecaca; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .ci-btn-del:hover { background: #fee2e2; }

    /* Empty state */
    .ci-empty { text-align: center; padding: 60px 20px; }
    .ci-empty .ci-empty-icon { width: 64px; height: 64px; background: #fdf2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 1.5rem; color: #800000; }

    /* Responsive */
    @media (max-width: 1024px) { .ci-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) {
        .ci-stats { grid-template-columns: repeat(2, 1fr); }
        .ci-filter-row { flex-wrap: wrap; }
    }
</style>

<div class="chat-index-wrap">

    <!-- Page Header -->
    <div class="ci-page-header">
        <div>
            <h1>
                <span class="icon-wrap"><i class="fas fa-comments"></i></span>
                Chat Management
            </h1>
            <p>View and respond to customer support conversations</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="ci-stats">
        <div class="ci-stat">
            <div class="ci-stat-icon total"><i class="fas fa-comments"></i></div>
            <div class="ci-stat-body">
                <div class="label">Total</div>
                <div class="value total">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="ci-stat">
            <div class="ci-stat-icon open"><i class="fas fa-check-circle"></i></div>
            <div class="ci-stat-body">
                <div class="label">Open</div>
                <div class="value open">{{ $stats['open'] }}</div>
            </div>
        </div>
        <div class="ci-stat">
            <div class="ci-stat-icon pending"><i class="fas fa-clock"></i></div>
            <div class="ci-stat-body">
                <div class="label">Pending</div>
                <div class="value pending">{{ $stats['pending'] }}</div>
            </div>
        </div>
        <div class="ci-stat">
            <div class="ci-stat-icon closed"><i class="fas fa-lock"></i></div>
            <div class="ci-stat-body">
                <div class="label">Closed</div>
                <div class="value closed">{{ $stats['closed'] }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="ci-filter">
        <form method="GET">
            <div class="ci-filter-row">
                <div class="f-group" style="max-width: 160px;">
                    <label>Status</label>
                    <select name="status">
                        <option value="all">All Statuses</option>
                        <option value="open"    {{ request('status') === 'open'    ? 'selected' : '' }}>Open</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="closed"  {{ request('status') === 'closed'  ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="f-group">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Search by name, email, or subject…" value="{{ request('search') }}">
                </div>
                <div class="f-actions">
                    <button type="submit" class="btn-filter-search">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.chats.index') }}" class="btn-filter-reset">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="ci-table-card">
        @if($chats->count() > 0)
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Messages</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chats as $chat)
                            @php
                                $displayName = $chat->user_name ?: ($chat->user?->name ?? 'Guest');
                                $displayEmail = $chat->user_email ?: ($chat->user?->email ?? 'N/A');
                            @endphp
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div class="ci-avatar">{{ strtoupper(substr($displayName, 0, 1)) }}</div>
                                        <div>
                                            <div style="font-weight:600; font-size:0.875rem; color:#1a1a1a;">{{ $displayName }}</div>
                                            <div style="font-size:0.75rem; color:#9ca3af;">{{ $displayEmail }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="ci-subject">{{ Str::limit($chat->subject, 36) }}</div>
                                </td>
                                <td>
                                    <span class="ci-badge {{ $chat->status }}">{{ ucfirst($chat->status) }}</span>
                                    @if($chat->unreadCount() > 0)
                                        <span class="ci-unread"><i class="fas fa-circle" style="font-size:5px;"></i> {{ $chat->unreadCount() }} unread</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="ci-msg-count"><i class="fas fa-comment" style="font-size:0.6rem; margin-right:3px;"></i>{{ $chat->messages()->count() }}</span>
                                </td>
                                <td style="font-size:0.8rem; color:#6b7280;">{{ $chat->updated_at->diffForHumans() }}</td>
                                <td>
                                    <div style="display:flex; gap:6px;">
                                        <a href="{{ route('admin.chats.show', $chat) }}" class="ci-btn-view">
                                            <i class="fas fa-comments"></i> Open
                                        </a>
                                        <form action="{{ route('admin.chats.destroy', $chat) }}" method="POST" onsubmit="return confirm('Delete this chat?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ci-btn-del">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 16px 20px; border-top: 1px solid #f3f1ee;">
                {{ $chats->links() }}
            </div>
        @else
            <div class="ci-empty">
                <div class="ci-empty-icon"><i class="fas fa-inbox"></i></div>
                <h3 style="font-size:1.1rem; font-weight:700; color:#1a1a1a; margin-bottom:6px;">No chats found</h3>
                <p style="color:#6b7280; font-size:0.875rem;">No conversations match your current filters.</p>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.chats.index') }}" style="display:inline-block; margin-top:14px; padding:8px 20px; background:linear-gradient(135deg,#800000,#5a0000); color:#fff; border-radius:8px; font-size:0.875rem; font-weight:600; text-decoration:none;">Clear Filters</a>
                @endif
            </div>
        @endif
    </div>

</div>
@endsection
