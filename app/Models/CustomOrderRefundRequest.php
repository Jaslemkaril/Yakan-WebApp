<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomOrderRefundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_order_id',
        'user_id',
        'request_type',
        'reason',
        'details',
        'evidence_paths',
        'status',
        'admin_note',
        'requested_at',
        'reviewed_at',
        'processed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'evidence_paths' => 'array',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function customOrder(): BelongsTo
    {
        return $this->belongsTo(CustomOrder::class);
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
