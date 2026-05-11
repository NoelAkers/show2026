<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShowClassRequest extends FormRequest
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
        $sectionId = $this->route('show_section')->id;

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('show_classes')
                    ->where('show_section_id', $sectionId)
                    ->ignore($this->route('show_class')),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'prize_level_id' => ['required', 'exists:prize_levels,id'],
            'max_entries_per_exhibitor' => ['required', 'integer', 'min:1'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
