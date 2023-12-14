<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Purchase extends Model
{
    use HasFactory, Searchable;
    

    protected $guarded = [];

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
    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class, 'request_id', 'id');
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

    /**
     * Get all of the assets for the Purchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'purchase_id', 'id');
    }

    /**
     * Get the status that owns the Purchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PurchaseStatus::class, 'status_id', 'id');
    }

    /**
     * Get the type that owns the Purchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PurchaseType::class, 'type_id', 'id');
    }

    /**
     * Get all of the pictures for the Purchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function picture(): HasOne
    {
        return $this->hasOne(PurchasePicture::class, 'purchase_id', 'id');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => '',
            'purchased_from' => ''
        ];
    }
}
