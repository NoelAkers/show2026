<?php

namespace App\Http\Resources;

use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Entry */
class EntryResource extends JsonResource
{
    public function __construct(Entry $resource, private readonly int $showClassId)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'entry_number' => $this->entry_number,
            'exhibitor_name' => $this->exhibitor->full_name,
            'show_class_id' => $this->show_class_id,
            'show_class_name' => $this->showClass->name,
            'belongs_to_class' => $this->show_class_id === $this->showClassId,
        ];
    }
}
