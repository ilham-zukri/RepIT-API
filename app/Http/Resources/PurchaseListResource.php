<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "request_id"=> $this->request_id,
            "purchased_by"=> $this->buyer->full_name ?? $this->buyer->user_name,
            "purchased_from"=> $this->purchased_from,
            "items" => $this->items,
            "total_price"=> $this->total_price,
            "status_id"=> $this->status->status,
            "created_at"=> $this->created_at->format('d-m-Y'),
        ];
    }
}
