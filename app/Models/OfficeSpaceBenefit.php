<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeSpaceBenefit extends Model
{
    protected $fillable = [
        'name',
        'office_space_id',
    ];

    public function officeSpace(): BelongsTo
    {
        return $this->belongsTo(OfficeSpace::class);
    }
}
