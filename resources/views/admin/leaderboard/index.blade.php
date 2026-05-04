<x-layouts::app :title="__('Leaderboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Leaderboard</flux:heading>

            <form method="GET" action="{{ route('admin.leaderboard') }}" class="flex items-center gap-2">
                <flux:select name="section" class="w-48" onchange="this.form.submit()">
                    <flux:select.option value="">All Sections</flux:select.option>
                    @foreach ($sections as $section)
                        <flux:select.option value="{{ $section->id }}" :selected="$sectionId == $section->id">{{ $section->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                @if ($sectionId)
                    <flux:button :href="route('admin.leaderboard')" size="sm" wire:navigate>Clear</flux:button>
                @endif
            </form>
        </div>

        @if ($sectionId)
            @php $selectedSection = $sections->firstWhere('id', $sectionId); @endphp
            @if ($selectedSection)
                <p class="text-sm text-zinc-500">Showing points from <strong>{{ $selectedSection->name }}</strong> only.</p>
            @endif
        @endif

        @if ($leaderboard->isEmpty())
            <p class="text-sm text-zinc-500">No exhibitors found.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Rank</flux:table.column>
                    <flux:table.column>Exhibitor</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Points</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($leaderboard as $row)
                        <flux:table.row :key="$row['exhibitor']->id">
                            <flux:table.cell class="tabular-nums font-semibold">
                                {{ $row['rank'] }}
                            </flux:table.cell>
                            <flux:table.cell variant="strong">
                                <a href="{{ route('admin.exhibitors.show', $row['exhibitor']) }}" class="hover:underline" wire:navigate>
                                    {{ $row['exhibitor']->full_name }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell>{{ ucfirst($row['exhibitor']->type) }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($row['points'] > 0)
                                    <flux:badge color="green">{{ $row['points'] }}</flux:badge>
                                @else
                                    <flux:badge color="zinc">0</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
