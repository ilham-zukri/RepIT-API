<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasesDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'asset_type',
        'brand',
        'model',
        'amount',
        'price_ea',
        'total_price',
        'warranty_end',
        'purchase_id'
    ];

    /**
     * Get the user that owns the PurchasesDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function items(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }
}
