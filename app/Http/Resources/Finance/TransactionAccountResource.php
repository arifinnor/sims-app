<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Finance\TransactionAccount
 */
class TransactionAccountResource extends JsonResource
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
            'transaction_type_id' => $this->transaction_type_id,
            'role' => $this->role,
            'label' => $this->label,
            'direction' => $this->direction->value,
            'account_type' => $this->account_type->value,
            'chart_of_account_id' => $this->chart_of_account_id,
            'chart_of_account' => $this->whenLoaded('chartOfAccount', fn () => [
                'id' => $this->chartOfAccount->id,
                'code' => $this->chartOfAccount->code,
                'name' => $this->chartOfAccount->name,
                'account_type' => $this->chartOfAccount->account_type->value,
            ]),
        ];
    }
}
