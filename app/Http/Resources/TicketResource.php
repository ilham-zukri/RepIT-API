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
        $location = '';
        if($this->asset_id){
            $location = $this->asset->location->name;
        }else {
            $location = $this->createdBy->branch->name;
        }
        return [
            'id' => $this->id,
            'asset_id' => $this->asset_id,
            'priority' => $this->priority,
            'created_by' => $this->createdBy->full_name ?? $this->createdBy->user_name,
            'location' => $location,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'handler' => $this->handler->full_name ?? "#N/A",
            'status' => $this->status->status,
            'created_at' => $this->created_at->format('d-m-Y'),
            'images' => $this->images
        ];
    }
}
