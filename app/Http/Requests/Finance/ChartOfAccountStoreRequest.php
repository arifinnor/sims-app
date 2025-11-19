<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\AccountType;
use App\Enums\Finance\NormalBalance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChartOfAccountStoreRequest extends FormRequest
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
            'category_id' => ['sometimes', 'nullable', 'uuid', 'exists:account_categories,id'],
            'code' => ['required', 'string', 'max:50', Rule::unique('chart_of_accounts', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'parent_id' => ['sometimes', 'nullable', 'uuid', 'exists:chart_of_accounts,id'],
            'account_type' => ['required', 'string', Rule::in(AccountType::values())],
            'normal_balance' => ['required', 'string', Rule::in(NormalBalance::values())],
            'is_posting' => ['required', 'boolean'],
            'is_cash' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'category_id' => $this->normalizeUuid('category_id'),
            'parent_id' => $this->normalizeUuid('parent_id'),
            'is_posting' => $this->normalizeBoolean('is_posting'),
            'is_cash' => $this->normalizeBoolean('is_cash'),
            'is_active' => $this->normalizeBoolean('is_active'),
        ]);
    }

    private function normalizeUuid(string $key): ?string
    {
        $value = $this->input($key);

        if ($value === '' || $value === null) {
            return null;
        }

        return (string) $value;
    }

    private function normalizeBoolean(string $key): ?bool
    {
        if (! $this->has($key)) {
            return null;
        }

        return filter_var($this->input($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
