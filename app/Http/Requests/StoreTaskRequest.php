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
        'name' => $this->isMethod('post') ? ['required', 'string'] : ['sometimes', 'string'],
        'description' => ['nullable', 'string'],
        'due' => $this->isMethod('post') ? ['required', 'date'] : ['sometimes', 'date'],
        'priority' => $this->isMethod('post') ? ['required', 'string'] : ['sometimes', 'string'],
        'progress' => ['required', 'string'], // assuming progress is always required
        'tag' => $this->isMethod('post') ? ['required', 'string'] : ['sometimes', 'string'],
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
