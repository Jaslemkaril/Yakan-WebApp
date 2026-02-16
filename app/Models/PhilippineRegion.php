<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhilippineRegion extends Model
{
    protected $fillable = ['region_code', 'name'];

    public function provinces(): HasMany
    {
        return $this->hasMany(PhilippineProvince::class, 'region_id');
    }
}
