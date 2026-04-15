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
        'reason',
        'details',
        'evidence_paths',
        'status',
        'admin_note',
        'requested_at',
        'reviewed_at',
        'processed_at',
        'approved_amount',
        'reviewed_by',
    ];

    protected $casts = [
        'evidence_paths' => 'array',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'processed_at' => 'datetime',
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
