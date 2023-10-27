<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
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
            'asset_type' => $this->asset_type,
            'brand' => $this->brand,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'cpu' => $this->cpu,
            'ram' => $this->ram,
            'utilization' => $this->utilization,
            'location' => $this->location->name,
            'status' => $this->status->status,
            'qr_path' => $this->qrCode->path
        ];
    }
}
