<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchased_by',
        'purchased_at',
        'puruchased_from',
        'total_price',
        'requested_by',
    ];

    /**
     * Get the user that owns the Purchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by', 'id');
    }

    /**
     * Get the requester that owns the Purchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by', 'id');
    }

    /**
     * Get all of the items for the Purchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchasesDetail::class, 'purchase_id', 'id');
    }
}
