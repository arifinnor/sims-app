<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class ClassroomStoreRequest extends FormRequest
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
            'academic_year_id' => ['required', 'string', 'exists:academic_years,id'],
            'homeroom_teacher_id' => ['nullable', 'string', 'exists:teachers,id'],
            'grade_level' => ['required', 'integer', 'min:1', 'max:12'],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
