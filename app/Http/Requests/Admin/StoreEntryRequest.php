<?php

namespace App\Http\Requests\Admin;

use App\Models\Entry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEntryRequest extends FormRequest
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
            'exhibitor_id' => [
                'required',
                'integer',
                'exists:exhibitors,id',
                function ($attribute, $value, $fail) {
                    $showClass = $this->route('show_class');
                    $count = Entry::where('show_class_id', $showClass->id)
                        ->where('exhibitor_id', $value)
                        ->count();

                    if ($count >= $showClass->max_entries_per_exhibitor) {
                        $fail("This exhibitor has already reached the maximum of {$showClass->max_entries_per_exhibitor} ".($showClass->max_entries_per_exhibitor === 1 ? 'entry' : 'entries').' for this class.');
                    }
                },
            ],
        ];
    }
}
