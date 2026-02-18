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
        return $this->messages()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Get all payments for this chat
     */
    public function payments(): HasMany
    {
        return $this->hasMany(ChatPayment::class);
    }

    /**
     * Get the pending payment for this chat
     */
    public function pendingPayment()
    {
        return $this->payments()->where('status', 'pending')->first();
    }

    /**
     * Get the verified/paid payment for this chat
     */
    public function verifiedPayment()
    {
        return $this->payments()->where(function ($query) {
            $query->where('status', 'verified')->orWhere('status', 'paid');
        })->first();
    }

    /**
     * Check if customer has accepted the price quote
     */
    public function hasAcceptedQuote(): bool
    {
        return $this->messages()
            ->where('sender_type', 'user')
            ->where('message', 'like', '%Customer accepted the price quote%')
            ->exists();
    }
}
