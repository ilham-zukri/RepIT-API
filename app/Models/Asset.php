<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Asset extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'owner_id',
        'name',
        'asset_type',
        'brand',
        'model',
        'serial_number',
        'cpu',
        'ram',
        'utilization',
        'location_id',
        'qr_code',
        'deployed_at',
        'status_id',
        'purchase_id',
        'scrapped_at',
    ];

    public function toSearchableArray(): array
    {
        return [
            'name' => '',
            'id' => '',
        ];
    }

    /**
     * Get the user that owns the Asset
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    /**
     * Get the user that owns the Asset
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    /**
     * Get the user that owns the Asset
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

    /**
     * Get the status that owns the Asset
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(AssetStatus::class, 'status_id', 'id');
    }

    /**
     * Get the qrCode associated with the Asset
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function qrCode(): HasOne
    {
        return $this->hasOne(QrCode::class, 'qr_code_id', 'qr_code');
    }

    /**
     * Get all of the tickets for the Asset
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'asset_id', 'id');
    }

    /**
     * Get all of the spareParts for the Asset
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function spareParts(): HasMany
    {
        return $this->hasMany(SparePart::class, 'device_id', 'id');
    }

    protected $casts = [
        'deployed_at' => 'datetime',
        'scrapped_at' => 'datetime'
    ];
}
