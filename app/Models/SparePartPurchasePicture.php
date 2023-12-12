<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SparePartPurchasePicture extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Get the purchase that owns the SparePartPurchasePicture
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(SparePartPurchase::class, 'purchase_id');
    }
}
