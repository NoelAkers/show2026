<x-layouts::app :title="__('Exhibitors')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <flux:heading size="xl">Exhibitors</flux:heading>

        @if ($exhibitors->isEmpty())
            <p class="text-sm text-zinc-500">No exhibitors found.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Entries</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($exhibitors as $exhibitor)
                        <flux:table.row :key="$exhibitor->id">
                            <flux:table.cell variant="strong">{{ $exhibitor->full_name }}</flux:table.cell>
                            <flux:table.cell>{{ $exhibitor->totalEntries() }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="primary" :href="route('helper.exhibitors.add-entry', $exhibitor)" wire:navigate>
                                    Add Entries
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
