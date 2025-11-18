<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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
            'accountNumber' => $this->account_number,
            'fullAccountNumber' => $this->getFullAccountNumber(),
            'name' => $this->name,
            'type' => $this->type,
            'category' => $this->category,
            'parentAccountId' => $this->parent_account_id,
            'parentAccount' => $this->whenLoaded('parent', function () {
                return AccountResource::make($this->parent)->resolve();
            }),
            'children' => $this->whenLoaded('children', function () {
                return $this->children->map(fn ($child) => AccountResource::make($child)->resolve());
            }),
            'childrenCount' => $this->when(isset($this->children_count), $this->children_count),
            'balance' => (string) $this->balance,
            'currency' => $this->currency ?? 'IDR',
            'status' => $this->status,
            'description' => $this->description,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),

            'account_number' => $this->account_number,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
