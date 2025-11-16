<?php

namespace App\Http\Requests\Students;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentGuardianAttachRequest extends FormRequest
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
        /** @var Student $student */
        $student = $this->route('student');

        return [
            'guardian_id' => [
                'required',
                'uuid',
                Rule::exists('guardians', 'id'),
                Rule::unique('student_guardian', 'guardian_id')
                    ->where('student_id', $student->id),
            ],
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

            if ($this->boolean('is_primary')) {
                // Check if there's already a primary guardian for this student
                $hasPrimaryGuardian = $student->guardians()
                    ->wherePivot('is_primary', true)
                    ->exists();

                if ($hasPrimaryGuardian) {
                    $validator->errors()->add(
                        'is_primary',
                        'This student already has a primary guardian. Please unset the existing primary guardian first.'
                    );
                }
            }
        });
    }
}
