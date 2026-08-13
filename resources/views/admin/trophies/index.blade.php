<x-layouts::app :title="__('Trophies')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Trophies</flux:heading>
            <div class="flex gap-2">
                <flux:button :href="route('admin.trophies.create')" variant="primary" icon="plus" wire:navigate>
                    Add Trophy
                </flux:button>
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        @if ($trophies->isEmpty())
            <p class="text-sm text-zinc-500">No trophies yet. Create one to get started.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Classes / Personnel</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($trophies as $trophy)
                        <flux:table.row :key="$trophy->id">
                            <flux:table.cell variant="strong">
                                <div class="max-w-[16rem] truncate" title="{{ $trophy->name }}">{{ $trophy->name }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="max-w-xs truncate" title="{{ $trophy->description }}">{{ $trophy->description ?? '—' }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($trophy->is_points_based)
                                    <flux:badge color="blue">Points</flux:badge>
                                @else
                                    <flux:badge color="purple">Judge</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($trophy->is_points_based)
                                    {{ $trophy->showClasses->count() }} {{ Str::plural('class', $trophy->showClasses->count()) }}
                                @else
                                    <div class="space-y-0.5 text-sm">
                                        @if ($trophy->judge)
                                            <div><span class="text-zinc-400">Judge:</span> {{ $trophy->judge->name }}</div>
                                        @endif
                                        @if ($trophy->steward)
                                            <div><span class="text-zinc-400">Steward:</span> {{ $trophy->steward->name }}</div>
                                        @endif
                                        @if (! $trophy->judge && ! $trophy->steward)
                                            <span class="text-zinc-400">—</span>
                                        @endif
                                    </div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" :href="route('admin.trophies.edit', $trophy)" wire:navigate>Edit</flux:button>
                                    <form method="POST" action="{{ route('admin.trophies.destroy', $trophy) }}">
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
