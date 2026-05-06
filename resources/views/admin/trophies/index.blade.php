<x-layouts::app :title="__('Trophies')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Trophies</flux:heading>
            <flux:button :href="route('admin.trophies.create')" variant="primary" icon="plus" wire:navigate>
                Add Trophy
            </flux:button>
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
                    <flux:table.column>Classes</flux:table.column>
                    <flux:table.column>Current Winner(s)</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($trophies as $trophy)
                        @php $winners = $trophy->winners(); @endphp
                        <flux:table.row :key="$trophy->id">
                            <flux:table.cell variant="strong">{{ $trophy->name }}</flux:table.cell>
                            <flux:table.cell>{{ $trophy->description ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $trophy->showClasses->count() }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($winners->isEmpty())
                                    <span class="text-zinc-400">No winner yet</span>
                                @else
                                    {{ $winners->pluck('exhibitor')->pluck('full_name')->join(', ') }}
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
