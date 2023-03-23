<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
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
            'purchased_by' => $this->purchased_by,
            'purchased_from' => $this->purchased_from,
            'items' => $this->whenLoaded('items', function(){
                return $this->items;
            }),
            'total_price' => $this->total_price,
            'requested_by' => $this->requested_by
        ];
    }
}
