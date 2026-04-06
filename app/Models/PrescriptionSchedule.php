<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionSchedule extends Model
{
    protected $fillable = ['prescription_id', 'time_of_day', 'label'];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}
