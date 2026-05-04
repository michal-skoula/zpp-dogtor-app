<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drug extends Model
{
    protected $fillable = ['name', 'form', 'strength', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}
