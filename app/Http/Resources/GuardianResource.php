<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardianResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'relationship' => $this->relationship,
            'address' => $this->address,
            'createdAt' => $this->created_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),
            'pivot' => $this->when($this->pivot, [
                'relationshipType' => $this->pivot->relationship_type ?? null,
                'relationship_type' => $this->pivot->relationship_type ?? null,
                'isPrimary' => (bool) ($this->pivot->is_primary ?? false),
                'is_primary' => (bool) ($this->pivot->is_primary ?? false),
            ]),
        ];
    }
}
