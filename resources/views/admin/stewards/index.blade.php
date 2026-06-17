<x-layouts::app :title="__('Stewards')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Stewards</flux:heading>
            <flux:button :href="route('admin.stewards.create')" variant="primary" icon="plus" wire:navigate>
                Add Steward
            </flux:button>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        @if ($stewards->isEmpty())
            <p class="text-sm text-zinc-500">No stewards yet. Add one to get started.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Phone</flux:table.column>
                    <flux:table.column>Sections Assigned</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($stewards as $steward)
                        <flux:table.row :key="$steward->id">
                            <flux:table.cell variant="strong">{{ $steward->name }}</flux:table.cell>
                            <flux:table.cell>{{ $steward->email }}</flux:table.cell>
                            <flux:table.cell>{{ $steward->phone ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $steward->assigned_sections_count }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" :href="route('admin.stewards.edit', $steward)" wire:navigate>Edit</flux:button>
                                    <form method="POST" action="{{ route('admin.stewards.destroy', $steward) }}">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button size="sm" variant="danger" type="submit">Remove</flux:button>
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
