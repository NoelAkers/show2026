<x-layouts::app :title="$showClass->name">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <flux:button :href="route('admin.show-sections.show-classes.index', $showSection)" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                    {{ $showSection->name }}
                </flux:button>
                <flux:heading size="xl">{{ $showClass->name }}</flux:heading>
            </div>
            <flux:button :href="route('admin.show-sections.show-classes.edit', [$showSection, $showClass])" wire:navigate>Edit Class</flux:button>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        @if ($showClass->description)
            <p class="max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">{{ $showClass->description }}</p>
        @endif

        <div class="flex items-center gap-4 text-sm text-zinc-500">
            <span>Max entries per exhibitor: <strong class="text-zinc-900 dark:text-zinc-100">{{ $showClass->max_entries_per_exhibitor }}</strong></span>
            <span>Total entries: <strong class="text-zinc-900 dark:text-zinc-100">{{ $showClass->entries->count() }}</strong></span>
        </div>

        {{-- Add Entry Form --}}
        <div class="max-w-lg rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="lg" class="mb-3">Add Entry</flux:heading>
            @if ($exhibitors->isEmpty())
                <p class="text-sm text-zinc-500">No exhibitors registered yet.</p>
            @else
                <form method="POST" action="{{ route('admin.show-sections.show-classes.entries.store', [$showSection, $showClass]) }}" class="flex items-end gap-3">
                    @csrf
                    <flux:field class="flex-1">
                        <flux:label>Exhibitor</flux:label>
                        <flux:select name="exhibitor_id">
                            <flux:select.option value="">Select exhibitor…</flux:select.option>
                            @foreach ($exhibitors as $exhibitor)
                                <flux:select.option value="{{ $exhibitor->id }}">{{ $exhibitor->sort_name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('exhibitor_id') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                    <flux:button type="submit" variant="primary">Add</flux:button>
                </form>
            @endif
        </div>

        {{-- Entries List --}}
        <div>
            <flux:heading size="lg" class="mb-3">Entries</flux:heading>

            @if ($showClass->entries->isEmpty())
                <p class="text-sm text-zinc-500">No entries yet.</p>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>#</flux:table.column>
                        <flux:table.column>Exhibitor</flux:table.column>
                        <flux:table.column>Type</flux:table.column>
                        <flux:table.column>Result</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($showClass->entries as $entry)
                            <flux:table.row :key="$entry->id">
                                <flux:table.cell class="tabular-nums">{{ $entry->entry_number }}</flux:table.cell>
                                <flux:table.cell variant="strong">
                                    <a href="{{ route('admin.exhibitors.show', $entry->exhibitor) }}" class="hover:underline" wire:navigate>
                                        {{ $entry->exhibitor->full_name }}
                                    </a>
                                </flux:table.cell>
                                <flux:table.cell>{{ ucfirst($entry->exhibitor->type) }}</flux:table.cell>
                                <flux:table.cell>
                                    @if ($entry->result)
                                        <flux:badge color="green">{{ $entry->result->placementLabel() }}</flux:badge>
                                    @else
                                        <flux:badge color="zinc">No result</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @unless ($entry->hasResult())
                                        <form method="POST" action="{{ route('admin.show-sections.show-classes.entries.destroy', [$showSection, $showClass, $entry]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button size="sm" variant="danger" type="submit">Delete</flux:button>
                                        </form>
                                    @endunless
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </div>
    </div>
</x-layouts::app>
