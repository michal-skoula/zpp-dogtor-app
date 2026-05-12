<?php

namespace App\Models;

use Database\Factories\PathFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Path extends Model
{
    /** @use HasFactory<PathFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'going_moves',
        'button_press_moves',
        'return_moves',
    ];

    public function journeys(): HasMany
    {
        return $this->hasMany(Journey::class);
    }
}
