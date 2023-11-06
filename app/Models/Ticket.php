<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;
    protected $guarded =[];

    /**
     * Get the priority that owns the Ticket
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'priority_id', 'id');
    }

    /**
     * Get the asset that owns the Ticket
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'id');
    }

    /**
     * Get the category that owns the Ticket
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id', 'id');
    }

    /**
     * Get the handler that owns the Ticket
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handler_id', 'id');
    }

    /**
     * Get the status that owns the Ticket
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id', 'id');
    }
}
