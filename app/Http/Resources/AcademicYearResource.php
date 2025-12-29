<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicYearResource extends JsonResource
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
            'startDate' => $this->start_date?->toDateString(),
            'start_date' => $this->start_date?->toDateString(),
            'endDate' => $this->end_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'isActive' => $this->is_active,
            'is_active' => $this->is_active,
            'createdAt' => $this->created_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),
            'classrooms' => $this->whenLoaded('classrooms', function () {
                return $this->classrooms->map(fn ($classroom) => ClassroomResource::make($classroom)->resolve());
            }),
        ];
    }
}
