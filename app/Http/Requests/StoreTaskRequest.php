<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'due' => ['required', 'date'],
            'priority' => ['required', 'string'],
            'progress' => ['required', 'string'],
            'tag' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'due.required' => 'The date is required.',
            'priority.required' => 'The priority is required.',
            'progress.required' => 'The progress is required.',
            'tag.required' => 'A tag is required.',
        ];
    }
}
