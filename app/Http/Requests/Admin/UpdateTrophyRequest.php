<?php

namespace App\Http\Requests\Admin;

use App\Enums\TrophyRestriction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTrophyRequest extends FormRequest
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
            'description' => ['nullable', 'string', 'max:255'],
            'is_points_based' => ['boolean'],
            'class_ids' => ['nullable', 'array', 'required_if:is_points_based,1'],
            'class_ids.*' => ['integer', 'exists:show_classes,id'],
            'judge_id' => ['nullable', 'integer', 'exists:users,id', 'required_if:is_points_based,0'],
            'steward_id' => ['nullable', 'integer', 'exists:users,id'],
            'winning_entry_id' => ['nullable', 'integer', 'exists:entries,id'],
            'restrictions' => ['nullable', 'array'],
            'restrictions.*' => ['string', new Enum(TrophyRestriction::class)],
        ];
    }
}
