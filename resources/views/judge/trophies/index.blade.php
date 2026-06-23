<x-layouts::app :title="__('My Trophies')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <flux:heading size="xl">My Trophies</flux:heading>

        @if ($trophies->isEmpty())
            <p class="text-sm text-zinc-500">No trophies assigned. Please contact an administrator.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Trophy</flux:table.column>
                    <flux:table.column>Winning Entry</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($trophies as $trophy)
                        <flux:table.row :key="$trophy->id">
                            <flux:table.cell variant="strong">
                                <a href="{{ route('judge.trophies.show', $trophy) }}" class="hover:underline" wire:navigate>{{ $trophy->name }}</a>
                                @if ($trophy->description)
                                    <div class="text-sm font-normal text-zinc-500">{{ $trophy->description }}</div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($trophy->winning_entry_id)
                                    {{ $trophy->winningEntry->formatted_entry_number }}
                                @else
                                    <span class="text-zinc-400">No winner yet</span>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
