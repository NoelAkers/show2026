<div class="flex flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
                <flux:button :href="route('admin.exhibitors.show', $exhibitor)" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                    {{ $exhibitor->full_name }}
                </flux:button>
            @elseif(auth()->user()->isHelper())
                <flux:button :href="route('helper.exhibitors.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                    Exhibitors
                </flux:button>
            @endif
            <flux:heading size="xl">Add Entry for {{ $exhibitor->full_name }}</flux:heading>
        </div>
        @if(auth()->user()->isAdmin())
            @if ($exhibitor->entries->isNotEmpty())
                <flux:button :href="route('admin.exhibitors.labels', $exhibitor)" target="_blank" icon="printer" size="sm">Print All Labels</flux:button>
            @endif
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
        <div>
            <flux:button wire:click="save" variant="primary" wire:loading.attr="disabled" wire:target="save" class="mb-4">
                <span wire:loading.remove wire:target="save">Save Entries</span>
                <span wire:loading wire:target="save">Saving…</span>
            </flux:button>

            <div
                class="flex flex-col gap-2"
                x-data="{
                    openSection: null,
                    dirty: false,
                    _navigating: false,
                    init() {
                        this._savedHandler = () => { this.dirty = false; };
                        this._navigateHandler = (event) => {
                            if (!this.dirty || this._navigating) return;
                            if (!confirm('You have unsaved changes. Leave without saving?')) {
                                event.preventDefault();
                                return;
                            }
                            this._navigating = true;
                        };
                        this._beforeUnloadHandler = (event) => {
                            if (this.dirty) event.preventDefault();
                        };
                        window.addEventListener('entries-saved', this._savedHandler);
                        document.addEventListener('livewire:navigate', this._navigateHandler);
                        window.addEventListener('beforeunload', this._beforeUnloadHandler);
                    },
                    destroy() {
                        window.removeEventListener('entries-saved', this._savedHandler);
                        document.removeEventListener('livewire:navigate', this._navigateHandler);
                        window.removeEventListener('beforeunload', this._beforeUnloadHandler);
                    }
                }"
            >
                @foreach ($sections as $section)
                    <div
                        class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700"
                        wire:key="section-{{ $section->id }}"
                    >
                        <button
                            type="button"
                            @click="openSection = openSection === {{ $section->id }} ? null : {{ $section->id }}"
                            class="flex w-full items-center justify-between bg-zinc-50 px-4 py-3 text-left transition-colors hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700/75"
                        >
                            <flux:heading size="lg">{{ $section->name }}</flux:heading>
                            <flux:icon.chevron-down class="size-4 text-zinc-400 transition-transform duration-200" x-bind:class="openSection === {{ $section->id }} ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="openSection === {{ $section->id }}" x-transition:enter="transition duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="divide-y divide-zinc-100 dark:divide-zinc-700">
                            @foreach ($section->showClasses as $class)
                                <div
                                    class="flex items-center justify-between gap-4 px-4 py-3"
                                    wire:key="class-{{ $class->id }}"
                                >
                                    <span class="text-sm"><span class="tabular-nums text-zinc-600 dark:text-zinc-400">{{ $class->id }}.</span> {{ $class->name }}</span>
                                    <div class="flex items-center gap-1">
                                        <flux:button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            icon="minus"
                                            wire:click="decrement({{ $class->id }})"
                                            @click="dirty = true"
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
                                            @click="dirty = true"
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

            <div class="mt-4">
                <flux:button wire:click="save" variant="primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Save Entries</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </flux:button>
            </div>
        </div>
    @endif

    {{-- Current entries table --}}
    <div>
        <flux:heading size="lg" class="mb-3">Current Entries</flux:heading>

        @if ($exhibitor->entries->isEmpty())
            <p class="text-sm text-zinc-500">No entries yet.</p>
        @else
            <div class="overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Entry no.</flux:table.column>
                    <flux:table.column>Section</flux:table.column>
                    <flux:table.column>Class</flux:table.column>
                    <flux:table.column>Placement</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($exhibitor->entries as $entry)
                        <flux:table.row :key="$entry->id">
                            <flux:table.cell class="tabular-nums">{{ $entry->formatted_entry_number }}</flux:table.cell>
                            <flux:table.cell>{{ $entry->showClass->showSection->name }}</flux:table.cell>
                            <flux:table.cell variant="strong">
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.show-sections.show-classes.show', [$entry->showClass->showSection, $entry->showClass]) }}" class="hover:underline" wire:navigate>
                                        <span class="tabular-nums text-zinc-600 dark:text-zinc-400 font-normal">{{ $entry->showClass->id }}.</span> {{ $entry->showClass->name }}
                                    </a>
                                @else
                                    <span class="tabular-nums text-zinc-600 dark:text-zinc-400 font-normal">{{ $entry->showClass->id }}.</span> {{ $entry->showClass->name }}
                                @endif
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
            </div>
        @endif
    </div>
</div>
