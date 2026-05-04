<?php

namespace App\Http\Requests;

use App\Models\Result;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResultRequest extends FormRequest
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
            'entry_id' => ['required', 'integer', 'exists:entries,id'],
            'placement' => ['nullable', Rule::in(['1st', '2nd', '3rd', 'highly_commended'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $placement = $this->input('placement') ?: null;

            if (! in_array($placement, ['1st', '2nd', '3rd'])) {
                return;
            }

            $showClass = $this->route('show_class');

            $conflict = Result::whereHas(
                'entry',
                fn ($q) => $q->where('show_class_id', $showClass->id)
            )
                ->where('placement', $placement)
                ->exists();

            if ($conflict) {
                $label = match ($placement) {
                    '1st' => '1st place',
                    '2nd' => '2nd place',
                    '3rd' => '3rd place',
                };
                $validator->errors()->add('placement', "A {$label} result already exists in this class.");
            }
        });
    }
}
