<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestListResource extends JsonResource
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
            'requester'=> $this->requester->full_name ?? $this->requester->user_name,
            'status' => $this->status,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority->priority,
            'for_user' => $this->forUser->full_name ?? $this->forUser->user_name,
            'location' => $this->location->name,
            'created_at' => $this->created_at,
            'approved_at' => $this->approved_at ?? '#N/A'

        ];
    }
}
