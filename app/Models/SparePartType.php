<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SparePartType extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded =[];

    /**
     * Get all of the spareParts for the SparePartType
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function spareParts(): HasMany
    {
        return $this->hasMany(SparePart::class, 'type_id', 'id');
    }
}
