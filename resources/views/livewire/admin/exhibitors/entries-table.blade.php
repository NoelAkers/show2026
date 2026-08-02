<div>
    @if ($entries->isEmpty())
        <p class="text-sm text-zinc-500">No entries yet.</p>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'entry_number'" :direction="$sortDirection" wire:click="sort('entry_number')">Entry no.</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'section'" :direction="$sortDirection" wire:click="sort('section')">Section</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'class'" :direction="$sortDirection" wire:click="sort('class')">Class</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'placement'" :direction="$sortDirection" wire:click="sort('placement')">Placement</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($entries as $entry)
                    <flux:table.row :key="$entry->id">
                        <flux:table.cell class="tabular-nums">{{ $entry->formatted_entry_number }}</flux:table.cell>
                        <flux:table.cell>{{ $entry->showClass->showSection->name }}</flux:table.cell>
                        <flux:table.cell variant="strong">
                            <a href="{{ route('admin.show-sections.show-classes.show', [$entry->showClass->showSection, $entry->showClass]) }}" class="hover:underline" wire:navigate>
                                {{ $entry->showClass->id }}. {{ $entry->showClass->name }}
                            </a>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($entry->result?->placement)
                                <flux:badge color="{{ $entry->result->badgeColour() }}">{{ $entry->result->placementLabel() }}</flux:badge>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>
