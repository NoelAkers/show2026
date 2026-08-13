<x-layouts::app :title="__('Show Sections')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Show Sections</flux:heading>
            <div class="flex items-center gap-2">
                <flux:button :href="route('admin.class-cards')" variant="ghost" icon="printer">
                    Class Cards
                </flux:button>
                <flux:button :href="route('admin.result-cards')" variant="ghost" icon="printer">
                    Result Cards
                </flux:button>
                <flux:button :href="route('admin.show-sections.create')" variant="primary" icon="plus" wire:navigate>
                    Add Section
                </flux:button>
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        @if ($sections->isEmpty())
            <p class="text-sm text-zinc-500">No sections yet. Create one to get started.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column>Sort Order</flux:table.column>
                    <flux:table.column>Classes</flux:table.column>
                    <flux:table.column>Judge</flux:table.column>
                    <flux:table.column>Steward</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($sections as $section)
                        <flux:table.row :key="$section->id">
                            <flux:table.cell variant="strong">
                                <div class="max-w-[16rem] truncate" title="{{ $section->name }}">
                                    <a href="{{ route('admin.show-sections.show-classes.index', $section) }}" class="hover:underline" wire:navigate>
                                        {{ $section->name }}
                                    </a>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="max-w-xs truncate" title="{{ $section->description }}">{{ $section->description ?? '—' }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $section->sort_order }}</flux:table.cell>
                            <flux:table.cell>{{ $section->showClasses()->count() }}</flux:table.cell>
                            <flux:table.cell>
                                <a href="{{ route('admin.judges.index') }}" class="hover:underline" wire:navigate>
                                    {{ $section->judges->isNotEmpty() ? $section->judges->pluck('name')->join(', ') : 'TBC' }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell>
                                <a href="{{ route('admin.stewards.index') }}" class="hover:underline" wire:navigate>
                                    {{ $section->stewards->isNotEmpty() ? $section->stewards->pluck('name')->join(', ') : 'TBC' }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" :href="route('admin.show-sections.edit', $section)" wire:navigate>Edit</flux:button>
                                    <form method="POST" action="{{ route('admin.show-sections.destroy', $section) }}">
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
