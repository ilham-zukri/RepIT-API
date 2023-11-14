<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SparePartPurchaseDetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $table = 'spare_part_purchase_items';

    /**
     * Get the purchase that owns the SparePartPurchaseDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(SparePartPurchase::class, 'purchase_id');
    }

    /**
     * Get the type that owns the SparePartPurchaseDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(SparePartType::class, 'type_id');
    }

    
}
