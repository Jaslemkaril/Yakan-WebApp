<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatPayment extends Model
{
    protected $fillable = [
        'chat_id',
        'custom_order_id',
        'amount',
        'payment_method',
        'payment_proof',
        'status',
        'verified_at',
        'admin_notes',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the chat this payment belongs to
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the custom order created from this payment
     */
    public function customOrder(): BelongsTo
    {
        return $this->belongsTo(CustomOrder::class);
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment has been verified
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if payment was rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get the payment method display name
     */
    public function getPaymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'online_banking' => '💳 GCash',
            'bank_transfer' => '🏦 Bank Transfer',
            default => 'Unknown',
        };
    }

    /**
     * Get the status display name
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => '⏳ Pending',
            'paid' => '✅ Paid',
            'verified' => '✔️ Verified',
            'rejected' => '❌ Rejected',
            default => 'Unknown',
        };
    }
}
