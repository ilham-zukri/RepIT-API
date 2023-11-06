<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_id' => $this->asset_id,
            'priority' => $this->priority->priority,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category->category,
            'handler' => $this->handler->full_name ?? "#N/A",
            'status_id' => $this->status_id,
            'created_at' => $this->created_at->format('d-m-Y'),
            'images' => $this->images
        ];
    }
}
