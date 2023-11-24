<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'user_name' => $this->user_name,
            'role' => [
                'role_name' => $this->role->role_name,
                'asset_request' => $this->role->asset_request,
                'asset_approval' => $this->role->asset_approval,
                'knowledge_base' => $this->role->knowledge_base,
                'user_management' => $this->role->user_management,
                'asset_purchasing' => $this->role->asset_purchasing,
                'asset_management' => $this->role->asset_management
            ],
            'employee_id' => $this->employee_id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'branch' => $this->branch->name,
            'department' => $this->department->department,
            'active' => $this->active,
            'created_at' => $this->created_at->format('d/m/Y'),
        ];
    }
}
