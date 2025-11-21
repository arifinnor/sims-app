<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class JournalEntryIndexRequest extends FormRequest
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
            'per_page' => ['sometimes', 'integer', 'in:'.implode(',', config('pagination.per_page_options'))],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', 'string', 'in:DRAFT,POSTED,VOID'],
            'cursor' => ['sometimes', 'nullable', 'string'],
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
            'per_page' => (int) ($validated['per_page'] ?? config('pagination.default')),
            'search' => $validated['search'] ?? null,
            'status' => $validated['status'] ?? null,
            'cursor' => $validated['cursor'] ?? null,
        ];
    }
}
