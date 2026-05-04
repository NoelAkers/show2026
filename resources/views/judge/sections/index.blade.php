<x-layouts::app :title="__('My Sections')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <flux:heading size="xl">My Sections</flux:heading>

        @if ($sections->isEmpty())
            <p class="text-sm text-zinc-500">No sections assigned. Please contact an administrator.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Section</flux:table.column>
                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($sections as $section)
                        <flux:table.row :key="$section->id">
                            <flux:table.cell variant="strong">
                                <a href="{{ route('judge.sections.show', $section) }}" class="hover:underline" wire:navigate>{{ $section->name }}</a>
                            </flux:table.cell>
                            <flux:table.cell>{{ $section->description ?? '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button :href="route('judge.sections.show', $section)" size="sm" wire:navigate>Enter Results</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
