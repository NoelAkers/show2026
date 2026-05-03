<x-layouts::app :title="'Add Entry — ' . $exhibitor->full_name">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <flux:button :href="route('admin.exhibitors.show', $exhibitor)" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                    {{ $exhibitor->full_name }}
                </flux:button>
                <flux:heading size="xl">Add Entry</flux:heading>
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        @php
            $classesBySection = $sections->mapWithKeys(fn ($s) => [
                $s->id => $s->showClasses->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values(),
            ]);
        @endphp

        <div
            class="max-w-lg rounded-lg border border-zinc-200 p-4 dark:border-zinc-700"
            x-data="{
                sectionId: '',
                classId: '',
                classesBySection: {{ Js::from($classesBySection) }},
                get classes() { return this.sectionId ? (this.classesBySection[this.sectionId] ?? []) : []; },
                resetClass() { this.classId = ''; }
            }"
        >
            <flux:heading size="lg" class="mb-3">Add Entry</flux:heading>

            @if ($sections->isEmpty())
                <p class="text-sm text-zinc-500">No sections or classes available.</p>
            @else
                <form method="POST" action="{{ route('admin.exhibitors.store-entry', $exhibitor) }}" class="flex flex-col gap-4">
                    @csrf

                    <flux:field>
                        <flux:label>Section</flux:label>
                        <flux:select x-model="sectionId" @change="resetClass()" name="_section">
                            <option value="">Select section…</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Class</flux:label>
                        <flux:select name="show_class_id" x-model="classId" x-bind:disabled="!sectionId">
                            <option value="">Select class…</option>
                            <template x-for="cls in classes" :key="cls.id">
                                <option :value="cls.id" x-text="cls.name"></option>
                            </template>
                        </flux:select>
                        @error('show_class_id') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>

                    <div>
                        <flux:button type="submit" variant="primary" x-bind:disabled="!classId">Add Entry</flux:button>
                    </div>
                </form>
            @endif
        </div>

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
</x-layouts::app>
