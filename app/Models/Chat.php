<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_phone',
        'subject',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user associated with the chat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all messages in this chat
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get unread messages count
     */
    public function unreadCount(): int
    {
        return $this->messages()->where('is_read', false)->where('sender_type', 'user')->count();
    }

    /**
     * Get the latest message
     */
    public function latestMessage()
    {
        return $this->messages()->latest()->first();
    }
}
