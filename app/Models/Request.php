<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Request extends Model
{
    use HasFactory;

    protected $fillable =[
        'requester_id',
        'status',
        'title',
        'description',
        'priority',
        'for_user',
        'location_id'
    ];

    /**
     * Get the requester that owns the Request
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id', 'id');
    }

    /**
     * Get all of the purchases for the Request
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'request_id', 'id');
    }
}
