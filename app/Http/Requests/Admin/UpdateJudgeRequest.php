<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJudgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:filter', 'max:255', Rule::unique('users')->ignore($this->route('judge'))],
            'phone' => ['nullable', 'string', 'max:50'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['integer', 'exists:show_sections,id'],
        ];
    }
}
