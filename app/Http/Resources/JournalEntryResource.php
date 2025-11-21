<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
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
            'referenceNumber' => $this->reference_number,
            'transactionDate' => $this->transaction_date?->format('Y-m-d'),
            'description' => $this->description,
            'status' => $this->status?->value,
            'totalAmount' => $this->total_amount,
            'studentId' => $this->student_id,
            'createdById' => $this->created_by,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'transactionType' => $this->whenLoaded('type', function () {
                return [
                    'id' => $this->type->id,
                    'code' => $this->type->code,
                    'name' => $this->type->name,
                ];
            }),
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'name' => $this->student->name,
                    'studentNumber' => $this->student->student_number,
                ];
            }),
            'createdBy' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
        ];
    }
}
