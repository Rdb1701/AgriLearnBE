<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstructionalMaterialRequest extends FormRequest
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
            'classroom_id' => 'required|max:11', //'required|exists:classrooms,id'
            'uploaded_by'  => 'required|max:11', //'required|exists:users,id'
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'new_files'    => 'array',
            'new_files.*'  => 'file|mimes:pdf,doc,docx,ppt,pptx,txt,png,jpg,jpeg|max:20480', // adjust types/sizes
            'isOffline'    => 'nullable|boolean'
        ];
    }
}
