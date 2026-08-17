<x-layouts::public :title="__('Results')">
    <div class="flex flex-col gap-6">
        <flux:heading size="xl">Results</flux:heading>

        @forelse ($sections as $section)
            @if ($loop->first)
                <div class="flex flex-col gap-2" x-data="{ openSection: null }">
            @endif

                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between bg-zinc-50 px-4 py-3 text-left transition-colors hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700/75"
                        @click="openSection = (openSection === {{ $section->id }} ? null : {{ $section->id }})"
                    >
                        <flux:heading size="lg">{{ $section->name }}</flux:heading>
                        <flux:icon.chevron-down
                            class="size-4 text-zinc-400 transition-transform duration-200"
                            x-bind:class="openSection === {{ $section->id }} ? 'rotate-180' : ''"
                        />
                    </button>

                    <div
                        x-show="openSection === {{ $section->id }}"
                        x-transition:enter="transition duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="px-4 py-3"
                    >
                        @foreach ($section->showClasses as $class)
                            <div class="{{ $loop->last ? '' : 'mb-3' }}">
                                <p class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $class->id }}. {{ $class->name }}</p>

                                @if ($class->entries->isEmpty())
                                    <p class="mt-0.5 text-sm text-zinc-500 italic">No entries.</p>
                                @else
                                    <div class="mt-0.5 flex flex-col">
                                        @foreach ($class->entries->sortBy(fn ($e) => $e->result->placementRank()) as $entry)
                                            <div class="flex items-center gap-3 py-0.5 text-sm">
                                                <span class="w-36 shrink-0 truncate text-zinc-700 dark:text-zinc-300">{{ $entry->exhibitor->full_name }}</span>
                                                @if ($entry->result?->placement)
                                                    <flux:badge size="sm" color="{{ $entry->result->badgeColour() }}" class="w-32 shrink-0 justify-center">
                                                        {{ $entry->result->placementLabel() }}
                                                    </flux:badge>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            @if ($loop->last)
                </div>
            @endif
        @empty
            <flux:text class="text-zinc-500">Results have not been published yet.</flux:text>
        @endforelse
    </div>
</x-layouts::public>
