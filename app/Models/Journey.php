<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Journey extends Model
{
    protected $fillable = [
        'path_id',
        'dispatched_at',
        'status',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
    ];

    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }

    public function drugs(): BelongsToMany
    {
        return $this->belongsToMany(Drug::class)->withPivot('quantity');
    }
}
