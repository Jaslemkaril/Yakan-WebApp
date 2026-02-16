<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhilippineBarangay extends Model
{
    protected $fillable = ['city_id', 'barangay_code', 'name'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(PhilippineCity::class);
    }
}
