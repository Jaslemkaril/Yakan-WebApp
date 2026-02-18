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
        'message_type',
        'message',
        'form_data',
        'image_path',
        'reference_images',
        'file_path',
        'file_name',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'reference_images' => 'array',
        'form_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    protected $attributes = [
        'message_type' => 'text',
        'form_data' => null,
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
