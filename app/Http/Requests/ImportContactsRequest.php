<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportContactsRequest extends FormRequest
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
        $fileSize = config('imports.max_file_size');
        if (!is_numeric($fileSize)) {
            throw new \InvalidArgumentException("imports.max_file_size config value must be number");
        }
        return [
            'file' => ['required', 'file', 'mimes:xml', 'max:' . $fileSize],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'File is required.',
            'file.file' => 'File must be a valid upload.',
            'file.mimes' => 'File must be an XML file.',
            'file.max' => 'File may not be greater than 100 MB.',
        ];
    }
}
