<div class="flex flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <flux:button :href="route('admin.exhibitors.show', $exhibitor)" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                {{ $exhibitor->full_name }}
            </flux:button>
            <flux:heading size="xl">Add Entry</flux:heading>
        </div>
        @if ($exhibitor->entries->isNotEmpty())
            <flux:button :href="route('admin.exhibitors.labels', $exhibitor)" target="_blank" icon="printer" size="sm">Print All Labels</flux:button>
        @endif
    </div>

    {{-- Status messages --}}
    @if ($statusMessage)
        <flux:callout :variant="$statusIsSuccess ? 'success' : ''" :icon="$statusIsSuccess ? 'check-circle' : 'information-circle'">
            {{ $statusMessage }}
        </flux:callout>
    @endif

    @if ($errors->any())
        <flux:callout variant="danger" icon="x-circle">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </flux:callout>
    @endif

    {{-- Entry accordion --}}
    @if ($sections->isEmpty())
        <p class="text-sm text-zinc-500">No sections or classes available.</p>
    @else
        <div class="flex flex-col gap-2">
            @foreach ($sections as $section)
                <div
                    x-data="{ open: true }"
                    class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700"
                    wire:key="section-{{ $section->id }}"
                >
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center justify-between bg-zinc-50 px-4 py-3 text-left transition-colors hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700/75"
                    >
                        <flux:heading size="base">{{ $section->name }}</flux:heading>
                        <flux:icon.chevron-down class="size-4 text-zinc-400 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
                    </button>

                    <div x-show="open" x-transition:enter="transition duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="divide-y divide-zinc-100 dark:divide-zinc-700">
                        @foreach ($section->showClasses as $class)
                            <div
                                class="flex items-center justify-between gap-4 px-4 py-3"
                                wire:key="class-{{ $class->id }}"
                            >
                                <span class="text-sm">{{ $class->name }}</span>
                                <div class="flex items-center gap-1">
                                    <flux:button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        icon="minus"
                                        wire:click="decrement({{ $class->id }})"
                                        wire:loading.attr="disabled"
                                        :disabled="($quantities[$class->id] ?? 0) <= ($lockedCounts[$class->id] ?? 0)"
                                    />
                                    <span class="w-8 text-center text-sm tabular-nums font-medium">{{ $quantities[$class->id] ?? 0 }}</span>
                                    <flux:button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        icon="plus"
                                        wire:click="increment({{ $class->id }})"
                                        wire:loading.attr="disabled"
                                        :disabled="($quantities[$class->id] ?? 0) >= ($maxCounts[$class->id] ?? 1)"
                                    />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            <flux:button wire:click="save" variant="primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Save Entries</span>
                <span wire:loading wire:target="save">Saving…</span>
            </flux:button>
        </div>
    @endif

    {{-- Current entries table --}}
    <div>
        <flux:heading size="lg" class="mb-3">Current Entries</flux:heading>

        @if ($exhibitor->entries->isEmpty())
            <p class="text-sm text-zinc-500">No entries yet.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>#</flux:table.column>
                    <flux:table.column>Section</flux:table.column>
                    <flux:table.column>Class</flux:table.column>
                    <flux:table.column>Placement</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($exhibitor->entries as $entry)
                        <flux:table.row :key="$entry->id">
                            <flux:table.cell class="tabular-nums">{{ $entry->entry_number }}</flux:table.cell>
                            <flux:table.cell>{{ $entry->showClass->showSection->name }}</flux:table.cell>
                            <flux:table.cell variant="strong">
                                <a href="{{ route('admin.show-sections.show-classes.show', [$entry->showClass->showSection, $entry->showClass]) }}" class="hover:underline" wire:navigate>
                                    {{ $entry->showClass->name }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($entry->result)
                                    <flux:badge color="green">{{ $entry->result->placementLabel() }}</flux:badge>
                                @else
                                    <flux:badge color="zinc">Pending</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
