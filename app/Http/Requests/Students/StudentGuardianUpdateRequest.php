<?php

namespace App\Http\Requests\Students;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;

class StudentGuardianUpdateRequest extends FormRequest
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
            'relationship_type' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Student $student */
            $student = $this->route('student');
            /** @var \App\Models\Guardian $guardian */
            $guardian = $this->route('guardian');

            if ($this->boolean('is_primary')) {
                // Check if there's already another primary guardian for this student
                $hasOtherPrimaryGuardian = $student->guardians()
                    ->wherePivot('is_primary', true)
                    ->where('guardians.id', '!=', $guardian->id)
                    ->exists();

                if ($hasOtherPrimaryGuardian) {
                    $validator->errors()->add(
                        'is_primary',
                        'This student already has a primary guardian. Please unset the existing primary guardian first.'
                    );
                }
            }
        });
    }
}
