<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class TransactionStoreRequest extends FormRequest
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
            'type_code' => ['required', 'string', 'exists:transaction_types,code'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['sometimes', 'nullable', 'string'],
            'cash_account_id' => ['sometimes', 'nullable', 'uuid', 'exists:chart_of_accounts,id'],
            'student_id' => ['sometimes', 'nullable', 'uuid', 'exists:students,id'],
            'external_ref' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}

