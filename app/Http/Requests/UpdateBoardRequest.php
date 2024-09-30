<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoardRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:boards,name,NULL,id,user_id,' . auth()->id()],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
        ];
    }
}