<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SparePartRequest extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Get the requester that owns the SparePartRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');   
    }

    /**
     * Get the status that owns the SparePartRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(RequestStatus::class, 'status_id');
    }

    /**
     * Get the purchase associated with the SparePartRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function purchase(): HasOne
    {
        return $this->hasOne(SparePartPurchase::class, 'request_id');
    }

    protected $casts = [
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
    ];  
}
