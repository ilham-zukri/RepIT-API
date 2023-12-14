<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class SparePartPurchase extends Model
{
    use HasFactory, Searchable;
    protected $guarded = [];

    /**
     * Get the request that owns the SparePartPurchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(SparePartRequest::class, 'request_id');
    }

    /**
     * Get the buyer that owns the SparePartPurchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by_id');
    }

    /**
     * Get the status that owns the SparePartPurchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PurchaseStatus::class, 'status_id');
    }

    /**
     * Get all of the items for the SparePartPurchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(SparePartPurchaseDetail::class, 'purchase_id');
    }

    /**
     * Get all of the spareParts for the SparePartPurchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function spareParts(): HasMany
    {
        return $this->hasMany(SparePart::class, 'purchase_id', 'id');
    }

    /**
     * Get the picture associated with the SparePartPurchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function picture(): HasOne
    {
        return $this->hasOne(SparePartPurchasePicture::class, 'purchase_id', 'id');
    }
    public function toSearchableArray(): array
    {
        return [
            'id' => '',
            'purchased_from' => ''
        ];
    }
}
