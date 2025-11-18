<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\AccountType;
use Illuminate\Foundation\Http\FormRequest;

class AccountStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_number' => ['required', 'string', 'max:255', 'unique:accounts,account_number'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:'.implode(',', AccountType::values())],
            'category' => ['nullable', 'string', 'max:255'],
            'parent_account_id' => ['nullable', 'uuid', 'exists:accounts,id'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'status' => ['nullable', 'string', 'in:active,inactive,archived'],
            'description' => ['nullable', 'string'],
        ];
    }
}
