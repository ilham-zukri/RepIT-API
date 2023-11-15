<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SparePart extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Get the type that owns the SparePart
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(SparePartType::class, 'type_id');
    }

    /**
     * Get the purchase that owns the SparePart
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(SparePartPurchase::class, 'purchase_id');
    }

    /**
     * Get the device that owns the SparePart
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'device_id');
    }

    /**
     * Get the status that owns the SparePart
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SparePartStatus::class, 'status_id');
    }
}
