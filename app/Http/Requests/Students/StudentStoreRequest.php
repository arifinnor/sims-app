<?php

namespace App\Http\Requests\Students;

use Illuminate\Foundation\Http\FormRequest;

class StudentStoreRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_number' => ['prohibited'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:students,email'],
            'phone' => ['nullable', 'string', 'max:255'],
            'guardians' => ['sometimes', 'array'],
            'guardians.*.guardian_id' => ['sometimes', 'string', 'exists:guardians,id'],
            'guardians.*.name' => ['sometimes', 'string', 'max:255'],
            'guardians.*.email' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'guardians.*.phone' => ['nullable', 'string', 'max:255'],
            'guardians.*.relationship' => ['nullable', 'string', 'max:255'],
            'guardians.*.address' => ['nullable', 'string'],
            'guardians.*.relationship_type' => ['nullable', 'string', 'max:255'],
            'guardians.*.is_primary' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $guardians = $this->input('guardians', []);

            if (is_array($guardians)) {
                $primaryCount = 0;
                foreach ($guardians as $index => $guardian) {
                    // Validate that either guardian_id or name is provided
                    if (! isset($guardian['guardian_id']) && ! isset($guardian['name'])) {
                        $validator->errors()->add(
                            "guardians.{$index}",
                            'Either guardian_id (for existing) or name (for new) must be provided.'
                        );
                    }

                    // Validate that name is required when creating new guardian
                    if (! isset($guardian['guardian_id']) && empty($guardian['name'])) {
                        $validator->errors()->add(
                            "guardians.{$index}.name",
                            'The name field is required when creating a new guardian.'
                        );
                    }

                    if (isset($guardian['is_primary']) && $guardian['is_primary']) {
                        $primaryCount++;
                    }
                }

                if ($primaryCount > 1) {
                    $validator->errors()->add(
                        'guardians',
                        'Only one guardian can be set as primary.'
                    );
                }
            }
        });
    }
}
