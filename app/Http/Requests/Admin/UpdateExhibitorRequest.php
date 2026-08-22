<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExhibitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_resident' => $this->boolean('is_resident'),
            'is_novice' => $this->boolean('is_novice'),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email:filter', 'max:255'],
            'type' => ['required', 'in:adult,junior'],
            'is_resident' => ['boolean'],
            'is_novice' => ['boolean'],
        ];
    }
}
