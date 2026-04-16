<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderRefundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'refund_type',
        'reason',
        'comment',
        'details',
        'evidence_paths',
        'status',
        'workflow_status',
        'system_validation',
        'fraud_flags',
        'fraud_risk_level',
        'recommended_decision',
        'recommended_refund_amount',
        'return_required',
        'final_decision',
        'refund_amount',
        'refund_channel',
        'refund_reference',
        'payout_status',
        'return_tracking_number',
        'return_shipped_at',
        'return_received_at',
        'admin_note',
        'requested_at',
        'reviewed_at',
        'processed_at',
        'approved_amount',
        'reviewed_by',
    ];

    protected $casts = [
        'evidence_paths' => 'array',
        'system_validation' => 'array',
        'fraud_flags' => 'array',
        'recommended_refund_amount' => 'decimal:2',
        'return_required' => 'boolean',
        'refund_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'processed_at' => 'datetime',
        'return_shipped_at' => 'datetime',
        'return_received_at' => 'datetime',
        'approved_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
