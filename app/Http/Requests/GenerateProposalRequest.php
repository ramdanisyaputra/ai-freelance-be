<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateProposalRequest extends FormRequest
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
            'brief' => 'required|string|min:50',
            'user_brief' => 'nullable|string',
            'language' => 'nullable|string|in:en,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'brief.required' => 'Brief klien wajib diisi.',
            'brief.min' => 'Brief minimal 50 karakter.',
            'language.in' => 'Bahasa harus English (en) atau Indonesian (id).',
        ];
    }
}
