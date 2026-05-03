<?php

namespace App\Http\Requests\Admin;

use App\Models\Entry;
use App\Models\ShowClass;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreExhibitorEntryRequest extends FormRequest
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
        $exhibitor = $this->route('exhibitor');

        return [
            'show_class_id' => [
                'required',
                'integer',
                'exists:show_classes,id',
                function ($attribute, $value, $fail) use ($exhibitor) {
                    $class = ShowClass::find($value);
                    $count = Entry::where('show_class_id', $value)
                        ->where('exhibitor_id', $exhibitor->id)
                        ->count();

                    if ($count >= $class->max_entries_per_exhibitor) {
                        $fail("This exhibitor has reached the maximum of {$class->max_entries_per_exhibitor} ".($class->max_entries_per_exhibitor === 1 ? 'entry' : 'entries').' for this class.');
                    }
                },
            ],
        ];
    }
}
