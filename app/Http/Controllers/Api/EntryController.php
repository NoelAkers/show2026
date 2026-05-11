<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EntryResource;
use App\Models\Entry;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    public function lookup(Request $request, int $number): EntryResource
    {
        $entry = Entry::with(['exhibitor', 'showClass'])
            ->where('entry_number', $number)
            ->firstOrFail();

        return new EntryResource($entry, $request->integer('show_class_id'));
    }
}
