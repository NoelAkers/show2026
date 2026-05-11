<x-layouts::public :title="__('Schedule')">
    <div class="flex flex-col gap-6">
        <flux:heading size="xl">Show Schedule</flux:heading>

        @forelse ($sections as $section)
            <div>
                <flux:heading size="lg" class="mb-3">{{ $section->name }}</flux:heading>

                @if ($section->description)
                    <flux:text class="mb-3">{{ $section->description }}</flux:text>
                @endif

                @if ($section->showClasses->isEmpty())
                    <flux:text class="text-zinc-500">No classes in this section yet.</flux:text>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Class</flux:table.column>
                            <flux:table.column>Description</flux:table.column>
                            <flux:table.column>Max Entries</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($section->showClasses as $class)
                                <flux:table.row :key="$class->id">
                                    <flux:table.cell variant="strong">{{ $class->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $class->description ?? '—' }}</flux:table.cell>
                                    <flux:table.cell>{{ $class->max_entries_per_exhibitor }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </div>
        @empty
            <flux:text class="text-zinc-500">The schedule has not been published yet.</flux:text>
        @endforelse
    </div>
</x-layouts::public>
