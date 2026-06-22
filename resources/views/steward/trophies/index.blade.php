<x-layouts::app :title="__('My Trophies')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <flux:heading size="xl">My Trophies</flux:heading>

        @if ($trophies->isEmpty())
            <p class="text-sm text-zinc-500">No trophies assigned. Please contact an administrator.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Trophy</flux:table.column>
                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column>Winner</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($trophies as $trophy)
                        <flux:table.row :key="$trophy->id">
                            <flux:table.cell variant="strong">
                                <a href="{{ route('steward.trophies.show', $trophy) }}" class="hover:underline" wire:navigate>{{ $trophy->name }}</a>
                            </flux:table.cell>
                            <flux:table.cell>{{ $trophy->description ?? '—' }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($trophy->winning_entry_id)
                                    {{ $trophy->winningEntry->exhibitor->full_name }}
                                @else
                                    <span class="text-zinc-400">No winner yet</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button :href="route('steward.trophies.show', $trophy)" size="sm" wire:navigate>Record Winner</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
