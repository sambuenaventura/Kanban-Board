<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
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
            'attachment' => ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'],
        ];
    }


    public function messages(): array
    {
        return [
            'attachment.required' => 'Please attach a file.',
            'attachment.max' => 'The file size must not exceed 2 MB.',
            'attachment.mimes' => 'The file type must be JPG, PNG, or PDF.',
        ];
    }
}
