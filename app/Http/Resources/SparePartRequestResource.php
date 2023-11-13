<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SparePartRequestResource extends JsonResource
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
            'requester' => $this->requester->full_name ?? $this->requester->user_name,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->status,
            'created_at' => $this->created_at->format('d/m/Y | H:i'),
            'approved_at' => $this->approved_at ? $this->approved_at->format('d/m/Y | H:i') : null,
        ];
    }
}
