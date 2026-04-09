@extends('layouts.admin')

@section('title', 'Chat: ' . $chat->subject)
@section('hide_admin_header', '1')
@section('admin_content_padding', 'p-3 sm:p-4 pb-0 sm:pb-0')

@section('content')
<style>
    /* ─── Layout ─────────────────────────────────── */
    .cs-wrap {
        background: #f8f7f5;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        height: calc(100dvh - 5.75rem);
        min-height: 600px;
        border: 1px solid #e9e5e0;
        border-radius: 14px;
    }

    /* Top bar */
    .cs-topbar { display: flex; justify-content: space-between; align-items: center; padding: 12px 18px; background: rgba(255, 255, 255, 0.94); border-bottom: 1px solid #e9e5e0; gap: 12px; flex-wrap: wrap; position: sticky; top: 0; z-index: 25; backdrop-filter: blur(6px); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06); }
    .cs-topbar-left { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
    .cs-back { display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #6b7280; text-decoration: none; font-weight: 500; transition: color 0.2s; }
    .cs-back:hover { color: #800000; }
    .cs-title { font-size: 1.05rem; font-weight: 700; color: #1a1a1a; display: flex; align-items: center; gap: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cs-title .icon-wrap { width: 30px; height: 30px; background: linear-gradient(135deg, #800000, #5a0000); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.75rem; flex-shrink: 0; }
    .cs-topbar-right { display: flex; gap: 6px; align-items: center; flex-shrink: 0; }
    .cs-info-toggle { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #e5e7eb; background: #fff; color: #6b7280; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .cs-info-toggle:hover { color: #800000; border-color: #d9c1c1; background: #fdf8f8; }

    /* Status select */
    .cs-status-select { padding: 7px 12px; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 0.8rem; font-weight: 600; color: #374151; background: #f9fafb; cursor: pointer; outline: none; transition: border-color 0.2s; }
    .cs-status-select:focus { border-color: #800000; }
    .cs-btn-delete { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: #fff0f0; color: #dc2626; border: 1.5px solid #fecaca; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .cs-btn-delete:hover { background: #fee2e2; }

    /* ─── Body: sidebar + main ────────────────────── */
    .cs-body { display: flex; flex: 1; overflow: hidden; min-height: 0; }

    /* Sidebar */
    .cs-sidebar { width: 300px; min-height: 0; flex-shrink: 0; background: #fff; border-right: 1px solid #e9e5e0; overflow-y: auto; padding: 16px 14px 20px; display: flex; flex-direction: column; gap: 12px; }
    .cs-sidebar::-webkit-scrollbar { width: 6px; }
    .cs-sidebar::-webkit-scrollbar-track { background: transparent; }
    .cs-sidebar::-webkit-scrollbar-thumb { background: #d8d3cd; border-radius: 4px; }

    .cs-card { background: #fdf8f8; border: 1px solid #f0e8e8; border-radius: 12px; overflow: hidden; }
    .cs-card-head { display: flex; align-items: center; gap: 8px; padding: 12px 14px; border-bottom: 1px solid #f0e8e8; }
    .cs-card-head .ch-icon { width: 28px; height: 28px; border-radius: 7px; background: linear-gradient(135deg, #800000, #5a0000); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; }
    .cs-card-head span { font-size: 0.8rem; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.04em; }
    .cs-card-body { padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; }
    .cs-info-row { display: flex; flex-direction: column; gap: 2px; }
    .cs-info-row .ir-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; }
    .cs-info-row .ir-value { font-size: 0.82rem; color: #1f2937; font-weight: 500; word-break: break-all; }

    /* Status badge in sidebar */
    .cs-sbadge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; width: fit-content; }
    .cs-sbadge.open    { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .cs-sbadge.pending { background: #fffbeb; color: #92400e; border: 1px solid #fcd34d; }
    .cs-sbadge.closed  { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
    .cs-sbadge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; display: inline-block; }

    /* Right info drawer */
    .cs-drawer-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.35); opacity: 0; pointer-events: none; transition: opacity 0.24s ease; z-index: 70; }
    .cs-drawer-backdrop.open { opacity: 1; pointer-events: auto; }
    .cs-info-drawer { position: fixed; top: 0; right: 0; width: min(380px, 95vw); height: 100dvh; background: #f8f7f5; box-shadow: -12px 0 28px rgba(0, 0, 0, 0.2); border-left: 1px solid #e9e5e0; transform: translateX(100%); transition: transform 0.28s ease; z-index: 71; display: flex; flex-direction: column; }
    .cs-info-drawer.open { transform: translateX(0); }
    .cs-drawer-head { display: flex; align-items: center; justify-content: space-between; padding: 16px; border-bottom: 1px solid #e9e5e0; background: #fff; }
    .cs-drawer-title { font-size: 0.95rem; font-weight: 700; color: #111827; letter-spacing: 0.01em; }
    .cs-drawer-close { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; color: #6b7280; cursor: pointer; transition: all 0.2s; }
    .cs-drawer-close:hover { color: #800000; border-color: #d9c1c1; background: #fdf8f8; }
    .cs-drawer-content { padding: 14px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; }
    .cs-drawer-actions { display: flex; flex-direction: column; gap: 10px; }
    .cs-drawer-actions .cs-status-select { width: 100%; }
    .cs-drawer-actions .cs-btn-delete { width: 100%; justify-content: center; }

    /* Action buttons in sidebar */
    .cs-action-btn { display: flex; align-items: center; gap: 8px; padding: 9px 14px; border-radius: 9px; font-size: 0.82rem; font-weight: 600; text-decoration: none; cursor: pointer; border: none; transition: all 0.2s; width: 100%; text-align: left; }
    .cs-action-btn.primary   { background: linear-gradient(135deg, #800000, #5a0000); color: #fff; }
    .cs-action-btn.primary:hover   { opacity: 0.88; transform: translateY(-1px); }
    .cs-action-btn.secondary { background: #fff; color: #800000; border: 1.5px solid #e8d4d4; }
    .cs-action-btn.secondary:hover { background: #fdf2f2; }
    .cs-action-btn.green     { background: linear-gradient(135deg, #059669, #047857); color: #fff; }
    .cs-action-btn.green:hover     { opacity: 0.88; transform: translateY(-1px); }

    /* ─── Main chat area ──────────────────────────── */
    .cs-main { flex: 1; min-width: 0; min-height: 0; display: flex; flex-direction: column; overflow: hidden; }

    /* Messages scroll area */
    .cs-messages { flex: 1; min-height: 0; overflow-y: auto; padding: 24px 28px; background: #f8f7f5; display: flex; flex-direction: column; gap: 4px; overscroll-behavior: contain; }
    .cs-messages::-webkit-scrollbar { width: 5px; }
    .cs-messages::-webkit-scrollbar-track { background: transparent; }
    .cs-messages::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

    /* Message bubbles */
    .cs-msg { display: flex; gap: 10px; max-width: 78%; margin-bottom: 16px; }
    .cs-msg.admin { flex-direction: row-reverse; margin-left: auto; }
    .cs-msg .cs-avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; flex-shrink: 0; align-self: flex-end; }
    .cs-msg.user  .cs-avatar { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; }
    .cs-msg.admin .cs-avatar { background: linear-gradient(135deg, #800000, #5a0000); color: #fff; }
    .cs-msg-body { display: flex; flex-direction: column; gap: 4px; }
    .cs-msg.admin .cs-msg-body { align-items: flex-end; }
    .cs-msg-sender { font-size: 0.74rem; font-weight: 700; color: #9ca3af; padding: 0 6px; letter-spacing: 0.02em; }
    .cs-msg-bubble {
        padding: 13px 17px;
        border-radius: 18px;
        font-size: 0.95rem;
        line-height: 1.6;
        word-break: break-word;
        white-space: normal;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
        max-width: min(540px, 64vw);
    }
    .cs-msg-text { margin: 0; white-space: pre-line; line-height: inherit; letter-spacing: 0.01em; }
    .cs-msg.user .cs-msg-bubble {
        background: #ffffff;
        color: #1f2937;
        border: 1px solid #e5e7eb;
        border-bottom-left-radius: 6px;
    }
    .cs-msg.admin .cs-msg-bubble {
        background: linear-gradient(145deg, #8f0000 0%, #6b0000 55%, #550000 100%);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-bottom-right-radius: 6px;
    }
    .cs-msg-time { font-size: 0.73rem; color: #bdbdbd; padding: 0 6px; }
    .cs-msg.user .cs-msg-time { color: #9ca3af; }

    /* Form response data block */
    .cs-form-data { margin-top: 10px; padding: 10px; background: rgba(255,255,255,0.15); border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); }
    .cs-msg.user .cs-form-data { background: #f0fdf4; border-color: #bbf7d0; }
    .cs-form-data .fd-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
    .cs-msg.admin .cs-form-data .fd-label { color: rgba(255,255,255,0.8); }
    .cs-msg.user  .cs-form-data .fd-label { color: #065f46; }
    .cs-form-data .fd-item { font-size: 0.78rem; padding: 4px 0; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; gap: 6px; }
    .cs-form-data .fd-item:last-child { border-bottom: none; }
    .cs-form-data .fd-key { font-weight: 600; flex-shrink: 0; }
    .cs-msg.admin .cs-form-data .fd-key { color: rgba(255,255,255,0.75); }
    .cs-msg.user  .cs-form-data .fd-key { color: #374151; }

    /* Request details button */
    .cs-req-btn { margin-top: 8px; padding: 5px 12px; background: rgba(255,255,255,0.18); color: rgba(255,255,255,0.9); border: 1px solid rgba(255,255,255,0.3); border-radius: 7px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 5px; }
    .cs-req-btn:hover { background: rgba(255,255,255,0.28); }
    .cs-msg.user .cs-req-btn { background: #f0fdf4; color: #065f46; border-color: #86efac; }
    .cs-msg.user .cs-req-btn:hover { background: #dcfce7; }

    /* Empty messages */
    .cs-empty-msgs { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #9ca3af; gap: 10px; padding: 40px; }
    .cs-empty-msgs i { font-size: 2.5rem; }

    /* Closed notice */
    .cs-closed-notice { margin: 16px 24px; padding: 16px 20px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; display: flex; align-items: center; gap: 12px; }
    .cs-closed-notice i { font-size: 1.3rem; color: #9ca3af; }
    .cs-closed-notice p { font-size: 0.85rem; color: #6b7280; margin: 0; }
    .cs-closed-notice strong { color: #374151; }

    /* Reply bar */
    .cs-reply { border-top: 1px solid #e9e5e0; background: #fff; padding: 14px 20px; position: sticky; bottom: 0; z-index: 4; }
    .cs-image-preview { margin-bottom: 10px; display: none; }
    .cs-image-preview-inner { position: relative; display: inline-block; }
    .cs-image-preview img { max-height: 120px; border-radius: 10px; border: 2px solid #e5e7eb; box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
    .cs-img-clear { position: absolute; top: -8px; right: -8px; width: 24px; height: 24px; background: #fff; border: 2px solid #e5e7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.12); transition: all 0.2s; }
    .cs-img-clear:hover { background: #fee2e2; border-color: #fca5a5; }
    .cs-reply-row { display: flex; align-items: flex-end; gap: 8px; background: #f8f7f5; border: 2px solid #e9e5e0; border-radius: 24px; padding: 8px 10px; transition: border-color 0.2s; }
    .cs-reply-row:focus-within { border-color: #800000; background: #fff; }
    .cs-attach-btn { width: 34px; height: 34px; border-radius: 50%; background: #fff; border: 1.5px solid #e5e7eb; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.2s; }
    .cs-attach-btn:hover { background: #800000; border-color: #800000; color: #fff; }
    .cs-attach-btn svg { width: 16px; height: 16px; color: #9ca3af; transition: color 0.2s; }
    .cs-attach-btn:hover svg { color: #fff; }
    .cs-reply-textarea { flex: 1; border: none; background: transparent; resize: none; outline: none; font-size: 0.875rem; color: #1f2937; padding: 6px 4px; min-height: 22px; max-height: 120px; overflow-y: auto; }
    .cs-reply-textarea::placeholder { color: #c9c2bb; }
    .cs-send-btn { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #800000, #5a0000); border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.2s; }
    .cs-send-btn:hover { transform: scale(1.06); box-shadow: 0 4px 10px rgba(128,0,0,0.3); }
    .cs-send-btn svg { width: 15px; height: 15px; color: #fff; }

    /* Responsive */
    @media (max-width: 1024px) {
        .cs-wrap { height: calc(100dvh - 5rem); min-height: 560px; }
    }

    @media (max-width: 860px) { .cs-sidebar { width: 260px; } }
    @media (max-width: 640px) {
        .cs-wrap { height: auto; min-height: calc(100dvh - 7.25rem); border-radius: 10px; }
        .cs-body { flex-direction: column; }
        .cs-sidebar { width: 100%; border-right: none; border-bottom: 1px solid #e9e5e0; max-height: 42vh; overflow-y: auto; }
        .cs-topbar { padding: 10px 12px; }
        .cs-messages { padding: 16px; }
        .cs-reply { padding: 10px 12px; }
        .cs-info-drawer { width: 100vw; }
    }

    /* ─── Grouped messages ──────────────────────── */
    .cs-msg { margin-bottom: 3px; }
    .cs-msg.last-in-group { margin-bottom: 14px; }
    .cs-avatar-spacer { width: 32px; flex-shrink: 0; }
    .cs-msg.user  .cs-msg-bubble.no-tail { border-bottom-left-radius: 18px; }
    .cs-msg.admin .cs-msg-bubble.no-tail { border-bottom-right-radius: 18px; }

    /* ─── Date separator ───────────────────────── */
    .cs-date-sep { display: flex; align-items: center; gap: 10px; margin: 10px 0 6px; }
    .cs-date-sep::before, .cs-date-sep::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
    .cs-date-sep span { font-size: 0.67rem; font-weight: 700; color: #9ca3af; white-space: nowrap; padding: 2px 8px; background: #f8f7f5; border-radius: 10px; border: 1px solid #e5e7eb; letter-spacing: 0.03em; text-transform: uppercase; }

</style>

<div class="cs-wrap">

    <!-- Top Bar -->
    <div class="cs-topbar">
        <div class="cs-topbar-left">
            <a href="{{ route('admin.chats.index') }}" class="cs-back">
                <i class="fas fa-arrow-left" style="font-size:0.75rem;"></i> Back to Chats
            </a>
            <div class="cs-title">
                <span class="icon-wrap"><i class="fas fa-comments"></i></span>
                {{ $chat->subject }}
            </div>
        </div>
        <div class="cs-topbar-right">
            <button type="button" class="cs-info-toggle" onclick="toggleInfoDrawer(true)" title="Chat details">
                <i class="fas fa-info"></i>
            </button>
        </div>
    </div>

    <!-- Body -->
    <div class="cs-body">

        <!-- Sidebar -->
        <div class="cs-sidebar">
            <!-- Quick Actions -->
            <div class="cs-card">
                <div class="cs-card-head">
                    <div class="ch-icon"><i class="fas fa-bolt"></i></div>
                    <span>Actions</span>
                </div>
                <div class="cs-card-body" style="gap:8px;">
                    <button onclick="showQuoteModal()" class="cs-action-btn primary">
                        <i class="fas fa-dollar-sign" style="width:14px;"></i> Send Price Quote
                    </button>
                    @php
                        $chatOrder = \App\Models\CustomOrder::where('chat_id', $chat->id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                    @endphp
                    @if($chatOrder)
                        <a href="{{ route('admin.custom-orders.show', $chatOrder->id) }}" class="cs-action-btn green">
                            <i class="fas fa-box" style="width:14px;"></i> Custom Order {{ $chatOrder->display_ref }}
                        </a>
                    @endif
                    @if($chat->user_id)
                        <a href="{{ route('admin.users.show', $chat->user_id) }}" class="cs-action-btn secondary">
                            <i class="fas fa-user-circle" style="width:14px;"></i> View Customer
                        </a>
                    @endif
                    <a href="{{ route('admin.chats.index') }}" class="cs-action-btn secondary">
                        <i class="fas fa-list" style="width:14px;"></i> All Chats
                    </a>
                </div>
            </div>

        </div><!-- /sidebar -->

        <!-- Main Chat Area -->
        <div class="cs-main">

            <!-- Messages -->
            <div class="cs-messages" id="messagesContainer">
                {{-- Spacer: grows to fill empty space, pushing messages to the bottom --}}
                <div style="flex:1; min-height:0;"></div>
                @php $messagesArr = $messages->values(); @endphp
                @forelse($messagesArr as $message)
                    @php
                        $isAdmin     = $message->sender_type !== 'user';
                        $senderKey   = $isAdmin ? 'admin' : 'user';
                        $prevMsg     = $loop->index > 0 ? $messagesArr[$loop->index - 1] : null;
                        $nextMsg     = !$loop->last ? $messagesArr[$loop->index + 1] : null;
                        $prevKey     = $prevMsg ? ($prevMsg->sender_type !== 'user' ? 'admin' : 'user') : null;
                        $nextKey     = $nextMsg ? ($nextMsg->sender_type !== 'user' ? 'admin' : 'user') : null;
                        $isGrouped      = $prevKey === $senderKey;
                        $isLastInGroup  = $nextKey !== $senderKey || $loop->last;
                        $showDateSep    = !$prevMsg || !$message->created_at->isSameDay($prevMsg->created_at);
                        $msgTs = $message->created_at;
                        if ($msgTs->isToday())         { $timeLabel = $msgTs->format('g:i A'); }
                        elseif ($msgTs->isYesterday()) { $timeLabel = 'Yesterday ' . $msgTs->format('g:i A'); }
                        else                           { $timeLabel = $msgTs->format('M j, g:i A'); }
                        if ($isAdmin) {
                            $initials = 'AD';
                        } else {
                            $name     = $message->user?->name ?? $chat->user_name ?? 'C';
                            $parts    = preg_split('/\s+/', trim($name));
                            $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                        }
                        $imageUrl = null;
                        if ($message->image_path) {
                            $ip = $message->image_path;
                            if (str_starts_with($ip, 'http://') || str_starts_with($ip, 'https://') || str_starts_with($ip, 'data:image')) {
                                $imageUrl = $ip;
                            } elseif (str_starts_with($ip, 'storage/')) {
                                $imageUrl = asset($ip);
                            } else {
                                $imageUrl = asset('storage/' . $ip);
                            }
                        }
                    @endphp

                    @if($showDateSep)
                    <div class="cs-date-sep">
                        <span>{{ $msgTs->isToday() ? 'Today' : ($msgTs->isYesterday() ? 'Yesterday' : $msgTs->format('F j, Y')) }}</span>
                    </div>
                    @endif

                    <div class="cs-msg {{ $senderKey }}{{ $isGrouped ? ' grouped' : '' }}{{ $isLastInGroup ? ' last-in-group' : '' }}">
                        @if(!$isGrouped)
                        <div class="cs-avatar">{{ $initials }}</div>
                        @else
                        <div class="cs-avatar-spacer"></div>
                        @endif
                        <div class="cs-msg-body">
                            @if(!$isGrouped)
                            <div class="cs-msg-sender">{{ $isAdmin ? 'Admin' : ($message->user?->name ?? $chat->user_name ?? 'Customer') }}</div>
                            @endif
                            <div class="cs-msg-bubble{{ $isLastInGroup ? '' : ' no-tail' }}">
                                @if($imageUrl)
                                    <a href="{{ $imageUrl }}" target="_blank" style="display:block; margin-bottom:{{ $message->message ? '8px' : '0' }};">
                                        <img src="{{ $imageUrl }}" alt="Chat image"
                                             style="max-width:220px; max-height:180px; border-radius:10px; object-fit:cover; border:2px solid rgba(255,255,255,0.25); box-shadow:0 2px 6px rgba(0,0,0,0.12); transition:transform 0.2s; display:block;"
                                             onmouseover="this.style.transform='scale(1.02)'"
                                             onmouseout="this.style.transform='scale(1)'"
                                             onerror="this.style.display='none'">
                                    </a>
                                    @if(!$isAdmin)
                                    <button type="button" class="cs-req-btn" onclick="requestCustomOrderDetails({{ $message->id }})">
                                        <i class="fas fa-clipboard-list"></i> Request Details
                                    </button>
                                    @endif
                                @endif
                                @if($message->message)
                                    <p class="cs-msg-text">{{ $message->message }}</p>
                                @endif
                                @if(isset($message->message_type) && $message->message_type === 'form_response' && !empty($message->form_data['responses']))
                                    <div class="cs-form-data">
                                        <div class="fd-label"><i class="fas fa-check-circle"></i> Custom Order Details</div>
                                        @foreach($message->form_data['responses'] as $fieldName => $fieldValue)
                                            <div class="fd-item">
                                                <span class="fd-key">{{ ucwords(str_replace('_', ' ', $fieldName)) }}:</span>
                                                <span>{{ $fieldValue }}</span>
                                            </div>
                                        @endforeach
                                        <div style="font-size:0.68rem; margin-top:6px; opacity:0.7; font-style:italic;">
                                            <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($message->form_data['submitted_at'])->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @if($isLastInGroup)
                            <div class="cs-msg-time">{{ $timeLabel }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="cs-empty-msgs">
                        <svg viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:52px;height:52px;opacity:0.28;margin-bottom:4px;">
                            <rect x="6" y="12" width="46" height="34" rx="9" fill="#800000"/>
                            <path d="M6 36 L6 46 L18 46 Z" fill="#800000" opacity="0.6"/>
                            <circle cx="22" cy="29" r="3" fill="white"/><circle cx="33" cy="29" r="3" fill="white"/><circle cx="44" cy="29" r="3" fill="white"/>
                            <rect x="28" y="36" width="38" height="28" rx="7" fill="#800000" opacity="0.45"/>
                            <circle cx="39" cy="50" r="2.5" fill="white" opacity="0.8"/><circle cx="47" cy="50" r="2.5" fill="white" opacity="0.8"/><circle cx="55" cy="50" r="2.5" fill="white" opacity="0.8"/>
                        </svg>
                        <p style="font-size:0.88rem;font-weight:600;color:#6b7280;margin:0 0 2px;">No messages yet</p>
                        <p style="font-size:0.76rem;color:#9ca3af;margin:0;">Start the conversation below</p>
                    </div>
                @endforelse
            </div><!-- /messages -->

            @if($chat->status !== 'closed')
                <div class="cs-reply">
                    <form action="{{ route('admin.chats.reply', $chat) }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}" method="POST" enctype="multipart/form-data" id="replyForm">
                        @csrf
                        @if(request('auth_token'))
                            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                        @endif
                        <input type="hidden" name="reference_images[]" id="referenceImagesInput">
                        <div class="cs-image-preview" id="imagePreview">
                            <div class="cs-image-preview-inner">
                                <img id="previewImg" src="" alt="Preview">
                                <button type="button" class="cs-img-clear" onclick="clearImage()">
                                    <svg style="width:12px;height:12px;color:#ef4444;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="cs-reply-row">
                            <label for="image" class="cs-attach-btn" title="Attach image">
                                <input type="file" id="image" name="image" accept="image/*" style="display:none;" onchange="updateImagePreview(this)">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </label>
                            <textarea id="messageInput" name="message" required class="cs-reply-textarea" placeholder="Type a message…" rows="1" oninput="autoResize(this)"></textarea>
                            <button type="submit" class="cs-send-btn" title="Send">
                                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/></svg>
                            </button>
                        </div>
                        @error('message')
                            <p style="color:#dc2626; font-size:0.75rem; margin-top:6px; padding-left:12px;">{{ $message }}</p>
                        @enderror
                        @error('image')
                            <p style="color:#dc2626; font-size:0.75rem; margin-top:6px; padding-left:12px;">{{ $message }}</p>
                        @enderror
                    </form>
                </div>
            @else
                <div class="cs-closed-notice">
                    <i class="fas fa-lock"></i>
                    <p><strong>Chat Closed</strong> — Change the status above to reply.</p>
                </div>
            @endif

        </div><!-- /main -->
    </div><!-- /body -->

    <!-- Right Details Drawer -->
    <div class="cs-drawer-backdrop" id="infoDrawerBackdrop" onclick="toggleInfoDrawer(false)"></div>
    <aside class="cs-info-drawer" id="infoDrawer" aria-hidden="true">
        <div class="cs-drawer-head">
            <span class="cs-drawer-title">Chat Details</span>
            <button type="button" class="cs-drawer-close" onclick="toggleInfoDrawer(false)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="cs-drawer-content">
            <div class="cs-card">
                <div class="cs-card-head">
                    <div class="ch-icon"><i class="fas fa-user"></i></div>
                    <span>Customer</span>
                </div>
                <div class="cs-card-body">
                    <div class="cs-info-row">
                        <span class="ir-label">Name</span>
                        <span class="ir-value">{{ $chat->user_name ?? 'Guest' }}</span>
                    </div>
                    <div class="cs-info-row">
                        <span class="ir-label">Email</span>
                        <span class="ir-value">{{ $chat->user_email ?? 'N/A' }}</span>
                    </div>
                    @if($chat->user_phone ?? false)
                    <div class="cs-info-row">
                        <span class="ir-label">Phone</span>
                        <span class="ir-value">{{ $chat->user_phone }}</span>
                    </div>
                    @endif
                    <div class="cs-info-row" style="margin-top:4px;">
                        <span class="ir-label">Status</span>
                        <span class="cs-sbadge {{ $chat->status }}">{{ ucfirst($chat->status) }}</span>
                    </div>
                </div>
            </div>

            <div class="cs-card">
                <div class="cs-card-head">
                    <div class="ch-icon"><i class="fas fa-info-circle"></i></div>
                    <span>Chat Info</span>
                </div>
                <div class="cs-card-body">
                    <div class="cs-info-row">
                        <span class="ir-label">Created</span>
                        <span class="ir-value">{{ $chat->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="cs-info-row">
                        <span class="ir-label">Last Updated</span>
                        <span class="ir-value">{{ $chat->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="cs-info-row">
                        <span class="ir-label">Messages</span>
                        <span class="ir-value">{{ $messages->count() }} total &bull; {{ $chat->unreadCount() }} unread</span>
                    </div>
                </div>
            </div>

            <div class="cs-card">
                <div class="cs-card-head">
                    <div class="ch-icon"><i class="fas fa-sliders-h"></i></div>
                    <span>Chat Controls</span>
                </div>
                <div class="cs-card-body cs-drawer-actions">
                    <form action="{{ route('admin.chats.update-status', $chat) }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}" method="POST">
                        @csrf
                        @method('PATCH')
                        @if(request('auth_token'))
                            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                        @endif
                        <select name="status" onchange="this.form.submit()" class="cs-status-select">
                            <option value="open"    {{ $chat->status === 'open'    ? 'selected' : '' }}>Open</option>
                            <option value="pending" {{ $chat->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="closed"  {{ $chat->status === 'closed'  ? 'selected' : '' }}>Closed</option>
                        </select>
                    </form>
                    <form action="{{ route('admin.chats.destroy', $chat) }}{{ request('auth_token') ? '?auth_token=' . request('auth_token') : '' }}" method="POST" onsubmit="return confirm('Delete this chat?');">
                        @csrf
                        @method('DELETE')
                        @if(request('auth_token'))
                            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
                        @endif
                        <button type="submit" class="cs-btn-delete">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>
</div><!-- /wrap -->

<script>
    // ─── Scroll to bottom on load ──────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        var mc = document.getElementById('messagesContainer');
        if (mc) mc.scrollTop = mc.scrollHeight;
    });

    // ─── Right details drawer ─────────────────────────────────────────────
    function toggleInfoDrawer(open) {
        const drawer = document.getElementById('infoDrawer');
        const backdrop = document.getElementById('infoDrawerBackdrop');
        if (!drawer || !backdrop) return;

        drawer.classList.toggle('open', open);
        backdrop.classList.toggle('open', open);
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') toggleInfoDrawer(false);
    });

    // ─── Image Preview ─────────────────────────────────────────────────────
    function updateImagePreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    function clearImage() {
        document.getElementById('image').value = '';
        document.getElementById('imagePreview').style.display = 'none';
    }

    // ─── Auto-resize textarea ──────────────────────────────────────────────
    function autoResize(ta) {
        ta.style.height = 'auto';
        ta.style.height = Math.min(ta.scrollHeight, 120) + 'px';
    }

    // ─── Quote Modal ───────────────────────────────────────────────────────
    function showQuoteModal()  { document.getElementById('quoteModal').classList.remove('hidden'); }
    function closeQuoteModal() { document.getElementById('quoteModal').classList.add('hidden'); }
    function calculateQuoteTotal() {
        const mat   = parseFloat(document.getElementById('quoteMaterialCost').value) || 0;
        const pat   = parseFloat(document.getElementById('quotePatternFee').value)   || 0;
        const dis   = parseFloat(document.getElementById('quoteDiscount').value)     || 0;
        const total = mat + pat - dis;
        document.getElementById('quoteTotalDisplay').textContent = '₱' + total.toFixed(2);
        document.getElementById('quoteTotal').value = total;
        return total;
    }
    function sendQuote() {
        const mat   = parseFloat(document.getElementById('quoteMaterialCost').value) || 0;
        const pat   = parseFloat(document.getElementById('quotePatternFee').value)   || 0;
        const dis   = parseFloat(document.getElementById('quoteDiscount').value)     || 0;
        const total = calculateQuoteTotal();
        const desc  = document.getElementById('quoteDescription').value;
        if (total <= 0) { alert('Please enter valid pricing amounts'); return; }
        const customerImages = [];
        document.querySelectorAll('.cs-msg.user img').forEach(img => {
            if (img.src && !img.src.includes('data:image')) customerImages.push(img.src);
        });
        if (customerImages.length > 0) {
            document.querySelectorAll('input[name="reference_images[]"]').forEach(i => i.remove());
            const form = document.getElementById('replyForm');
            customerImages.forEach(url => {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'reference_images[]'; inp.value = url;
                form.insertBefore(inp, form.firstChild);
            });
        }
        let msg = `📋 PRICE QUOTE\n\n`;
        if (mat > 0) msg += `Material Cost: ₱${mat.toLocaleString('en-PH', {minimumFractionDigits: 2})}\n`;
        if (pat > 0) msg += `Pattern Fee:   ₱${pat.toLocaleString('en-PH', {minimumFractionDigits: 2})}\n`;
        if (dis > 0) msg += `Discount:      -₱${dis.toLocaleString('en-PH', {minimumFractionDigits: 2})}\n`;
        msg += `\n━━━━━━━━━━━━━━━━\nTotal: ₱${total.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
        if (desc.trim()) msg += `\n\nNotes:\n${desc}`;
        msg += `\n\nPlease review and let us know if you'd like to proceed.`;
        const ta = document.getElementById('messageInput');
        ta.value = msg;
        autoResize(ta);
        closeQuoteModal();
        document.getElementById('quoteMaterialCost').value = '';
        document.getElementById('quotePatternFee').value   = '';
        document.getElementById('quoteDiscount').value     = '';
        document.getElementById('quoteDescription').value  = '';
        calculateQuoteTotal();
        document.querySelector('.cs-reply')?.scrollIntoView({ behavior: 'smooth' });
    }

    // ─── Request Custom Order Details ──────────────────────────────────────
    function requestCustomOrderDetails(messageId) {
        if (!confirm('Send a details request form to the customer for this design?')) return;

        const urlParams = new URLSearchParams(window.location.search);
        const authToken = urlParams.get('auth_token');
        let url = `/admin/chats/{{ $chat->id }}/request-details/${messageId}`;
        if (authToken) url += `?auth_token=${encodeURIComponent(authToken)}`;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        })
        .then(r => {
            if (!r.ok) return r.text().then(t => { throw new Error(`HTTP ${r.status}: ${t.substring(0, 200)}`); });
            return r.json();
        })
        .then(data => {
            if (data.success) {
                alert('✓ Details request sent!');
                location.reload();
            } else {
                alert('Failed: ' + (data.message || data.error || 'Unknown error'));
            }
        })
        .catch(err => alert('❌ ' + (err.message || 'Network or server error')));
    }
</script>

<!-- Quote Modal -->
<div id="quoteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">Send Price Quote</h3>
            <button onclick="closeQuoteModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="space-y-4">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 border-2 border-green-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="fas fa-calculator text-green-600"></i> Price Breakdown
                </h4>
                <div class="space-y-2.5">
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-600 w-28 flex-shrink-0">Material Cost</label>
                        <div class="relative flex-1">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                            <input type="number" id="quoteMaterialCost" step="0.01" min="0" class="w-full border border-gray-300 focus:border-green-500 focus:ring-1 focus:ring-green-200 rounded-lg pl-6 pr-3 py-2 text-sm transition-all" placeholder="0.00" oninput="calculateQuoteTotal()">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-600 w-28 flex-shrink-0">Pattern Fee</label>
                        <div class="relative flex-1">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                            <input type="number" id="quotePatternFee" step="0.01" min="0" class="w-full border border-gray-300 focus:border-green-500 focus:ring-1 focus:ring-green-200 rounded-lg pl-6 pr-3 py-2 text-sm transition-all" placeholder="0.00" oninput="calculateQuoteTotal()">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-600 w-28 flex-shrink-0">Discount</label>
                        <div class="relative flex-1">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-red-400 text-sm">-₱</span>
                            <input type="number" id="quoteDiscount" step="0.01" min="0" class="w-full border border-gray-300 focus:border-red-400 focus:ring-1 focus:ring-red-200 rounded-lg pl-7 pr-3 py-2 text-sm transition-all text-red-600" placeholder="0.00" oninput="calculateQuoteTotal()">
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-3 mt-3 border-2 border-green-300">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-700">Total</span>
                        <span class="text-2xl font-bold text-green-600" id="quoteTotalDisplay">₱0.00</span>
                    </div>
                    <input type="hidden" id="quoteTotal" value="0">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Notes (optional)</label>
                <textarea id="quoteDescription" rows="3" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none text-sm" placeholder="Add any notes or conditions…"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button onclick="sendQuote()" class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold py-3 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i> Send Quote
                </button>
                <button onclick="closeQuoteModal()" class="px-6 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
