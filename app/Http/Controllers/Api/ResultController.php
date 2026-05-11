<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreResultsRequest;
use App\Models\Entry;
use App\Models\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    private const PLACEMENT_MAP = [
        'first' => '1st',
        'second' => '2nd',
        'third' => '3rd',
        'highlyCommended' => 'highly_commended',
    ];

    private const UNIQUE_PLACEMENTS = ['first', 'second', 'third'];

    public function store(StoreResultsRequest $request): JsonResponse|Response
    {
        $showClassId = $request->integer('show_class_id');
        $results = $request->collect('results');

        // Resolve entry numbers to Entry models for the given class.
        $entryNumbers = $results->pluck('entry_number');
        $entries = Entry::whereIn('entry_number', $entryNumbers)->get()->keyBy('entry_number');

        foreach ($entryNumbers as $number) {
            if (! $entries->has($number)) {
                return response()->json(['message' => "Entry {$number} does not exist."], 422);
            }

            if ($entries->get($number)->show_class_id !== $showClassId) {
                return response()->json(['message' => "Entry {$number} does not belong to this class."], 422);
            }
        }

        // Check for duplicate unique placements within the submission.
        foreach (self::UNIQUE_PLACEMENTS as $placement) {
            if ($results->where('placement', $placement)->count() > 1) {
                $label = ucfirst($placement);

                return response()->json(['message' => "{$label} place has been assigned to more than one entry."], 422);
            }
        }

        // Check existing DB results for this class.
        $existing = Result::whereHas('entry', fn ($q) => $q->where('show_class_id', $showClassId))
            ->whereIn('placement', ['1st', '2nd', '3rd'])
            ->pluck('placement')
            ->map(fn ($p) => array_flip(self::PLACEMENT_MAP)[$p])
            ->all();

        foreach ($existing as $placement) {
            if ($results->pluck('placement')->contains($placement)) {
                $labels = ['first' => 'First', 'second' => 'Second', 'third' => 'Third'];

                return response()->json(['message' => "{$labels[$placement]} place has already been awarded in this class."], 422);
            }
        }

        DB::transaction(function () use ($results, $entries, $request): void {
            foreach ($results as $row) {
                $entry = $entries->get($row['entry_number']);
                Result::create([
                    'entry_id' => $entry->id,
                    'entered_by_user_id' => $request->user()->id,
                    'placement' => self::PLACEMENT_MAP[$row['placement']],
                ]);
            }
        });

        return response()->noContent(201);
    }
}
