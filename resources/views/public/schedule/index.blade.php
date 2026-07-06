<x-layouts::public :title="__('Schedule')">
    <div class="flex flex-col gap-6">
        <flux:heading size="xl">Show Schedule</flux:heading>

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
                        @if ($section->description && $section->description !== $section->name)
                            <flux:text class="mb-3">{{ $section->description }}</flux:text>
                        @endif

                        @if ($section->showClasses->isEmpty())
                            <flux:text class="text-zinc-500">No classes in this section yet.</flux:text>
                        @else
                            <flux:table class="w-full">
                                <flux:table.columns>
                                    <flux:table.column class="w-8 sm:w-12"></flux:table.column>
                                    <flux:table.column class="w-40 sm:w-56">Class</flux:table.column>
                                    <flux:table.column>Description</flux:table.column>
                                </flux:table.columns>
                                <flux:table.rows>
                                    @foreach ($section->showClasses as $class)
                                        <flux:table.row :key="$class->id">
                                            <flux:table.cell variant="strong" class="align-top whitespace-normal wrap-break-word">{{ $class->id }}. </flux:table.cell>
                                            <flux:table.cell variant="strong" class="align-top whitespace-normal wrap-break-word">{{ $class->name }}</flux:table.cell>
                                            <flux:table.cell class="align-top whitespace-normal wrap-break-word">{{ $class->description ?? '—' }}</flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                </flux:table.rows>
                            </flux:table>
                        @endif
                    </div>
                </div>

            @if ($loop->last)
                </div>
            @endif
        @empty
            <flux:text class="text-zinc-500">The schedule has not been published yet.</flux:text>
        @endforelse
    </div>
</x-layouts::public>
