<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstructionalMaterialRequest extends FormRequest
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
            'classroom_id' => 'required|string|max:11',
            'description'  => 'required|string|max:255',
            'uploaded_by'  => 'required|string|max:255',
            'title'        => 'required|string|max:255',
            'file_type'    => 'required|string|max:255',
            'file_path'    => 'required|string|max:255',
            'issOfline'    => 'required|string|max:255'
        ];
    }
}
