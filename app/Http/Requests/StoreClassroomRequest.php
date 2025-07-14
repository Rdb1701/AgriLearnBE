<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest extends FormRequest
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
            'instructor_id' => 'required|max:255',
            'class_name'    => 'required|string|max:255',
            'subject'       => 'required|string|max:255',
            'section_code'  => 'required|string|unique:classrooms,section_code',
        ];
    }
}
