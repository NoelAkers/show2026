<x-layouts::app :title="__('Prize Levels')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Prize Levels</flux:heading>
            <flux:button :href="route('admin.prize-levels.create')" variant="primary" icon="plus" wire:navigate>
                Add Prize Level
            </flux:button>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        @if ($prizeLevels->isEmpty())
            <p class="text-sm text-zinc-500">No prize levels yet. Create one to get started.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>1st Place</flux:table.column>
                    <flux:table.column>2nd Place</flux:table.column>
                    <flux:table.column>3rd Place</flux:table.column>
                    <flux:table.column>Classes</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($prizeLevels as $prizeLevel)
                        <flux:table.row :key="$prizeLevel->id">
                            <flux:table.cell variant="strong">{{ $prizeLevel->name }}</flux:table.cell>
                            <flux:table.cell>£{{ number_format($prizeLevel->first_place_pence / 100, 2) }}</flux:table.cell>
                            <flux:table.cell>£{{ number_format($prizeLevel->second_place_pence / 100, 2) }}</flux:table.cell>
                            <flux:table.cell>£{{ number_format($prizeLevel->third_place_pence / 100, 2) }}</flux:table.cell>
                            <flux:table.cell>{{ $prizeLevel->show_classes_count }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" :href="route('admin.prize-levels.edit', $prizeLevel)" wire:navigate>Edit</flux:button>
                                    <form method="POST" action="{{ route('admin.prize-levels.destroy', $prizeLevel) }}">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button size="sm" variant="danger" type="submit">Delete</flux:button>
                                    </form>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
