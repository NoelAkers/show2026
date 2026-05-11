<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResultsRequest extends FormRequest
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
            'show_class_id' => ['required', 'integer', 'exists:show_classes,id'],
            'results' => ['required', 'array', 'min:1'],
            'results.*.entry_number' => ['required', 'integer'],
            'results.*.placement' => ['required', Rule::in(['first', 'second', 'third', 'highlyCommended'])],
        ];
    }
}
