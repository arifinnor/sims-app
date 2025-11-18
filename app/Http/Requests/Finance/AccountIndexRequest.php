<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\AccountType;
use Illuminate\Foundation\Http\FormRequest;

class AccountIndexRequest extends FormRequest
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
            'with_trashed' => ['sometimes', 'string', 'in:none,only,all'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', AccountType::values())],
            'status' => ['sometimes', 'nullable', 'string', 'in:active,inactive,archived'],
        ];
    }

    /**
     * Get validated data with defaults.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        return [
            'with_trashed' => $validated['with_trashed'] ?? 'none',
            'search' => $validated['search'] ?? null,
            'type' => $validated['type'] ?? null,
            'status' => $validated['status'] ?? null,
        ];
    }
}
