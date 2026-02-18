<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_id',
        'user_id',
        'sender_type',
        // 'message_type', // Temporarily commented - requires migration to run first
        'message',
        // 'form_data', // Temporarily commented - requires migration to run first
        'image_path',
        'reference_images',
        'file_path',
        'file_name',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'reference_images' => 'array',
        // 'form_data' => 'array', // Temporarily commented - requires migration to run first
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    protected $attributes = [
        // 'message_type' => 'text', // Temporarily commented - requires migration to run first
        // 'form_data' => null, // Temporarily commented - requires migration to run first
    ];

    /**
     * Get the chat this message belongs to
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the user who sent this message
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
