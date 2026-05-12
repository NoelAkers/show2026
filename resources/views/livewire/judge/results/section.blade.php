<div class="flex flex-col gap-4" x-data="{ openClass: null }">
    @if ($errors->any())
        <flux:callout variant="danger" icon="x-circle">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </flux:callout>
    @endif

    @if ($showSection->showClasses->isEmpty())
        <p class="text-sm text-zinc-500">No classes in this section yet.</p>
    @else
        <div class="flex flex-col gap-2">
            @foreach ($showSection->showClasses as $class)
                <div
                    class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700"
                    wire:key="class-{{ $class->id }}"
                >
                    <button
                        type="button"
                        class="flex w-full items-center justify-between bg-zinc-50 px-4 py-3 text-left transition-colors hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700/75"
                        @click="
                            let wasOpen = openClass === {{ $class->id }};
                            if (openClass !== null) $wire.saveClass(openClass);
                            openClass = wasOpen ? null : {{ $class->id }};
                        "
                    >
                        <div class="flex items-center gap-3">
                            <flux:heading size="base">{{ $class->name }}</flux:heading>
                            <flux:badge size="sm" color="zinc">{{ $class->entries->count() }} {{ Str::plural('entry', $class->entries->count()) }}</flux:badge>
                        </div>
                        <flux:icon.chevron-down
                            class="size-4 text-zinc-400 transition-transform duration-200"
                            x-bind:class="openClass === {{ $class->id }} ? 'rotate-180' : ''"
                        />
                    </button>

                    <div
                        x-show="openClass === {{ $class->id }}"
                        x-transition:enter="transition duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                    >
                        @if ($class->entries->isEmpty())
                            <p class="px-4 py-3 text-sm text-zinc-500">No entries in this class.</p>
                        @else
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>#</flux:table.column>
                                    <flux:table.column>Placement</flux:table.column>
                                    <flux:table.column>Notes</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($class->entries as $entry)
                                        <flux:table.row :key="$entry->id">
                                            <flux:table.cell class="tabular-nums">{{ $entry->entry_number }}</flux:table.cell>
                                            <flux:table.cell>
                                                <div class="flex items-center gap-4">
                                                    @foreach (['1st' => '1st', '2nd' => '2nd', '3rd' => '3rd', 'highly_commended' => 'HC', '' => 'None'] as $value => $label)
                                                        <label class="flex cursor-pointer items-center gap-1.5">
                                                            <input
                                                                type="radio"
                                                                wire:model="placements.{{ $entry->id }}"
                                                                value="{{ $value }}"
                                                                class="accent-zinc-700"
                                                            >
                                                            <span class="text-sm">{{ $label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <flux:input wire:model="notes.{{ $entry->id }}" placeholder="Notes…" size="sm" class="w-48" />
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
