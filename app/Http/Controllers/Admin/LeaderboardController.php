<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exhibitor;
use App\Models\ShowSection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(Request $request): View
    {
        $sectionId = $request->query('section');
        $sections = ShowSection::ordered()->get();

        $rows = Exhibitor::with(['entries.result', 'entries.showClass'])->get()
            ->map(fn (Exhibitor $exhibitor) => [
                'exhibitor' => $exhibitor,
                'points' => $exhibitor->entries
                    ->when($sectionId, fn ($entries) => $entries->filter(
                        fn ($entry) => $entry->showClass->show_section_id == $sectionId
                    ))
                    ->sum(fn ($entry) => $entry->result?->points() ?? 0),
            ])
            ->sortByDesc('points')
            ->values();

        $rank = 0;
        $prevPoints = null;
        $counter = 0;

        $leaderboard = $rows->map(function (array $row) use (&$rank, &$prevPoints, &$counter) {
            $counter++;
            if ($row['points'] !== $prevPoints) {
                $rank = $counter;
            }
            $prevPoints = $row['points'];

            return array_merge($row, ['rank' => $rank]);
        });

        return view('admin.leaderboard.index', compact('leaderboard', 'sections', 'sectionId'));
    }
}
