<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SparePartStatus extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Get all of the spareParts for the SparePartStatus
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function spareParts(): HasMany
    {
        return $this->hasMany(SparePart::class, 'status_id');
    }
}
