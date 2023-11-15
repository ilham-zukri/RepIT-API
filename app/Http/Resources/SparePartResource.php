<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SparePartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "type"=> $this->type->type,
            "brand"=> $this->brand,
            "model"=> $this->model,
            "serial_number"=> $this->serial_number,
            "device_id"=> $this->device_id,
            "created_at"=> $this->created_at->format('d/m/Y'),
            "purchase_id"=> $this->purchase_id,
            "qr_path"=> $this->qr_path,
            "status"=> $this->status->status
        ];
    }
}
