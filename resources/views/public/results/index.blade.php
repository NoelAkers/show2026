<x-layouts::public :title="__('Results')">
    <div class="flex flex-col gap-6">
        <flux:heading size="xl">Results</flux:heading>

        @forelse ($sections as $section)
            <div>
                <flux:heading size="lg" class="mb-3">{{ $section->name }}</flux:heading>

                @foreach ($section->showClasses as $class)
                    <div class="mb-4">
                        <flux:subheading class="mb-2">{{ $class->name }}</flux:subheading>

                        @if ($class->entries->isEmpty())
                            <flux:text class="text-zinc-500 italic">Results pending.</flux:text>
                        @else
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>Exhibitor</flux:table.column>
                                    <flux:table.column>Placement</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($class->entries->sortBy(fn ($e) => match($e->result?->placement) { '1st' => 1, '2nd' => 2, '3rd' => 3, default => 4 }) as $entry)
                                        <flux:table.row :key="$entry->id">
                                            <flux:table.cell variant="strong">{{ $entry->exhibitor->full_name }}</flux:table.cell>
                                            <flux:table.cell>
                                                @if ($entry->result?->placement)
                                                    <flux:badge color="{{ match($entry->result->placement) { '1st' => 'yellow', '2nd' => 'zinc', '3rd' => 'amber', default => 'blue' } }}">
                                                        {{ $entry->result->placementLabel() }}
                                                    </flux:badge>
                                                @endif
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        @endif
                    </div>
                @endforeach
            </div>
        @empty
            <flux:text class="text-zinc-500">Results have not been published yet.</flux:text>
        @endforelse
    </div>
</x-layouts::public>
