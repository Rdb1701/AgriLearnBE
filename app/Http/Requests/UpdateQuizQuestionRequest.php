<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizQuestionRequest extends FormRequest
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
            'quiz_title'                    => 'required|string|max:255',
            'classroom_id'                  => 'required|exists:classrooms,id',
            'created_by'                    => 'required|exists:users,id',
            'questions'                     => 'required|array|min:1',
            'questions.*.questions_text'    => 'required|string',
            'questions.*.options'           => 'required|array|min:2',
            'questions.*.correct_answer'    => 'required|string',
            'questions.*.difficulty_level'  => 'required|in:easy,medium,hard',
        ];
    }
}
