<div
    class="flex flex-col gap-4"
    x-data="{
        openClass: null,
        _navigating: false,
        init() {
            this._navigateHandler = async (event) => {
                if (this.openClass === null || this._navigating) return;
                event.preventDefault();
                this._navigating = true;
                await this.$wire.saveClass(this.openClass);
                Livewire.navigate(event.detail.url.href);
            };
            this._beforeUnloadHandler = (event) => {
                if (this.openClass !== null) event.preventDefault();
            };
            document.addEventListener('livewire:navigate', this._navigateHandler);
            window.addEventListener('beforeunload', this._beforeUnloadHandler);
        },
        destroy() {
            document.removeEventListener('livewire:navigate', this._navigateHandler);
            window.removeEventListener('beforeunload', this._beforeUnloadHandler);
        }
    }"
>
    @if ($showSection->showClasses->isEmpty())
        <p class="text-sm text-zinc-500">No classes in this section yet.</p>
    @else
        <div class="flex flex-col gap-2">
            @foreach ($showSection->showClasses as $class)
                @php $resultCount = $class->entries->filter(fn ($e) => $e->result)->count(); @endphp
                @if ($class->entries->isNotEmpty())
                <div
                    class="overflow-hidden rounded-lg border transition-colors"
                    :class="$wire.classErrors[{{ $class->id }}] ? 'border-red-400 dark:border-red-500' : 'border-zinc-200 dark:border-zinc-700'"
                    wire:key="class-{{ $class->id }}"
                >
                    <button
                        type="button"
                        class="flex w-full items-center justify-between bg-zinc-50 px-4 py-3 text-left transition-colors hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700/75"
                        @click="async () => {
                            let wasOpen = openClass === {{ $class->id }};
                            if (openClass !== null) {
                                await $wire.saveClass(openClass);
                                if ($wire.classErrors[openClass]) {
                                    return;
                                }
                            }
                            openClass = wasOpen ? null : {{ $class->id }};
                        }"
                    >
                        <div class="flex items-center gap-3">
                            <flux:heading size="base"><span class="tabular-nums text-zinc-600 dark:text-zinc-400 font-normal">{{ $class->id }}.</span> {{ $class->name }}</flux:heading>
                            <flux:badge size="sm" color="zinc">{{ $class->entries->count() }} {{ Str::plural('entry', $class->entries->count()) }}</flux:badge>
                            @if ($resultCount > 0)
                                <flux:badge size="sm" color="sky">{{ $resultCount }} {{ Str::plural('result', $resultCount) }}</flux:badge>
                            @endif
                            <template x-if="$wire.classErrors[{{ $class->id }}]">
                                <flux:badge size="sm" color="red">Fix errors to continue</flux:badge>
                            </template>
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
                        @if (isset($classErrors[$class->id]))
                            <div class="px-4 pt-4">
                                <flux:callout variant="danger" icon="x-circle">
                                    {{ $classErrors[$class->id] }}
                                </flux:callout>
                            </div>
                        @endif

                        @if ($class->entries->isEmpty())
                            <p class="px-4 py-3 text-sm text-zinc-500">No entries in this class.</p>
                        @else
                            <div class="overflow-x-auto">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>Entry no.</flux:table.column>
                                    <flux:table.column>Placement</flux:table.column>
                                    <flux:table.column>Notes</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($class->entries as $entry)
                                        <flux:table.row :key="$entry->id">
                                            <flux:table.cell class="tabular-nums">{{ $entry->formatted_entry_number }}</flux:table.cell>
                                            <flux:table.cell>
                                                <div class="flex flex-wrap gap-x-3 gap-y-1">
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
                                                <flux:input wire:model="notes.{{ $entry->id }}" placeholder="Notes…" size="sm" class="w-full min-w-32" />
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                            </div>
                        @endif

                        <div class="flex justify-end border-t border-zinc-100 px-4 py-3 dark:border-zinc-700">
                            <flux:button
                                type="button"
                                variant="ghost"
                                size="sm"
                                wire:click="discardClass({{ $class->id }})"
                            >
                                Discard changes
                            </flux:button>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
