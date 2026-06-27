<x-layouts::app :title="__('Leaderboard — :name', ['name' => $trophy->name])">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('admin.trophies.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>Trophies</flux:button>
            <div>
                <flux:heading size="xl">{{ $trophy->name }}</flux:heading>
                @if ($trophy->description)
                    <p class="text-sm text-zinc-500">{{ $trophy->description }}</p>
                @endif
            </div>
        </div>

        @if ($restrictions->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach ($restrictions as $restriction)
                    <flux:badge color="amber" size="sm">
                        {{ match($restriction) {
                            \App\Enums\TrophyRestriction::Resident => 'Residents only',
                            \App\Enums\TrophyRestriction::Novice   => 'Novices only',
                            \App\Enums\TrophyRestriction::Junior   => 'Juniors only',
                        } }}
                    </flux:badge>
                @endforeach
                <p class="text-sm text-zinc-500">Ineligible exhibitors are shown in grey for cross-reference.</p>
            </div>
        @endif

        @if ($rows->isEmpty())
            <p class="text-sm text-zinc-500">No points scored yet.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>#</flux:table.column>
                    <flux:table.column>Exhibitor</flux:table.column>
                    <flux:table.column>Points</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Resident</flux:table.column>
                    <flux:table.column>Novice</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($rows as $i => $row)
                        <flux:table.row :key="$row['exhibitor']->id" :class="$row['is_eligible'] ? '' : 'opacity-40'">
                            <flux:table.cell class="tabular-nums font-semibold">
                                {{ $i + 1 }}
                            </flux:table.cell>
                            <flux:table.cell variant="strong">
                                <a href="{{ route('admin.exhibitors.show', $row['exhibitor']) }}" class="hover:underline" wire:navigate>
                                    {{ $row['exhibitor']->full_name }}
                                </a>
                                @if (! $row['is_eligible'])
                                    <flux:badge color="red" size="sm" class="ml-1">Ineligible</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="green">{{ $row['points'] }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ ucfirst($row['exhibitor']->type) }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($row['exhibitor']->is_resident)
                                    <flux:badge color="green" size="sm">Yes</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">No</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($row['exhibitor']->is_novice)
                                    <flux:badge color="green" size="sm">Yes</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">No</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
