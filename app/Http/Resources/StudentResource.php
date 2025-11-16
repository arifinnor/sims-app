<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'studentNumber' => $this->student_number,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'createdAt' => $this->created_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),
            'guardians' => $this->whenLoaded('guardians', function () {
                return $this->guardians->map(fn ($guardian) => GuardianResource::make($guardian)->resolve());
            }),
        ];
    }
}
