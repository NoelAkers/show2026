<x-layouts::app :title="$showSection->name">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('judge.sections.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                My Sections
            </flux:button>
            <flux:heading size="xl">{{ $showSection->name }}</flux:heading>
        </div>

        @if ($classes->isEmpty())
            <p class="text-sm text-zinc-500">No classes in this section yet.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Class</flux:table.column>
                    <flux:table.column>Entries</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($classes as $class)
                        <flux:table.row :key="$class->id">
                            <flux:table.cell variant="strong">{{ $class->name }}</flux:table.cell>
                            <flux:table.cell>{{ $class->entries_count }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button :href="route('judge.results.index', [$showSection, $class])" size="sm" wire:navigate>Enter Results</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
