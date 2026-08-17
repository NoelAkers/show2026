<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePrizeLevelRequest extends FormRequest
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
            'first_place_pence' => ['required', 'integer', 'min:0'],
            'second_place_pence' => ['required', 'integer', 'min:0'],
            'third_place_pence' => ['required', 'integer', 'min:0'],
        ];
    }
}
