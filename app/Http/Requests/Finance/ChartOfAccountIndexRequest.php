<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChartOfAccountIndexRequest extends FormRequest
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
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'account_type' => ['sometimes', 'nullable', 'string', Rule::in(AccountType::values())],
            'category_id' => ['sometimes', 'nullable', 'uuid', 'exists:account_categories,id'],
            'is_posting' => ['sometimes', 'nullable', 'boolean'],
            'is_cash' => ['sometimes', 'nullable', 'boolean'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
            'with_trashed' => ['sometimes', 'string', 'in:none,all,only'],
            'view' => ['sometimes', 'string', 'in:tree,list'],
            'per_page' => ['sometimes', 'integer', 'in:'.implode(',', config('pagination.per_page_options'))],
            'cursor' => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_posting' => $this->normalizeBoolean('is_posting'),
            'is_cash' => $this->normalizeBoolean('is_cash'),
            'is_active' => $this->normalizeBoolean('is_active'),
        ]);
    }

    /**
     * Get validated data with defaults applied.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        return [
            'search' => $validated['search'] ?? null,
            'account_type' => $validated['account_type'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'is_posting' => $validated['is_posting'] ?? null,
            'is_cash' => $validated['is_cash'] ?? null,
            'is_active' => $validated['is_active'] ?? null,
            'with_trashed' => $validated['with_trashed'] ?? 'none',
            'view' => $validated['view'] ?? 'tree',
            'per_page' => (int) ($validated['per_page'] ?? config('pagination.default')),
            'cursor' => $validated['cursor'] ?? null,
        ];
    }

    private function normalizeBoolean(string $key): ?bool
    {
        if (! $this->has($key)) {
            return null;
        }

        $value = $this->input($key);

        if ($value === '' || $value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
