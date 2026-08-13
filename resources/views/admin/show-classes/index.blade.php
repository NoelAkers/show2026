<x-layouts::app :title="__('Classes — :section', ['section' => $showSection->name])">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <flux:button :href="route('admin.show-sections.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>Sections</flux:button>
                <flux:heading size="xl">{{ $showSection->name }} — Classes</flux:heading>
            </div>
            <flux:button :href="route('admin.show-sections.show-classes.create', $showSection)" variant="primary" icon="plus" wire:navigate>
                Add Class
            </flux:button>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        @if ($classes->isEmpty())
            <p class="text-sm text-zinc-500">No classes yet. Add one to get started.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-1/5">Name</flux:table.column>
                    <flux:table.column class="w-[35%]">Description</flux:table.column>
                    <flux:table.column class="w-[12%]">Max Entries</flux:table.column>
                    <flux:table.column class="w-[10%]">Sort Order</flux:table.column>
                    <flux:table.column class="w-[8%]">Entries</flux:table.column>
                    <flux:table.column class="w-[15%]"></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($classes as $class)
                        <flux:table.row :key="$class->id">
                            <flux:table.cell variant="strong" class="max-w-0 truncate" title="{{ $class->id }}. {{ $class->name }}">
                                <a href="{{ route('admin.show-sections.show-classes.show', [$showSection, $class]) }}" class="hover:underline" wire:navigate>{{ $class->id }}. {{ $class->name }}</a>
                            </flux:table.cell>
                            <flux:table.cell class="max-w-0 truncate" title="{{ $class->description }}">{{ $class->description ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $class->max_entries_per_exhibitor }}</flux:table.cell>
                            <flux:table.cell>{{ $class->sort_order }}</flux:table.cell>
                            <flux:table.cell>{{ $class->entries()->count() }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" :href="route('admin.show-sections.show-classes.edit', [$showSection, $class])" wire:navigate>Edit</flux:button>
                                    <form method="POST" action="{{ route('admin.show-sections.show-classes.destroy', [$showSection, $class]) }}">
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
