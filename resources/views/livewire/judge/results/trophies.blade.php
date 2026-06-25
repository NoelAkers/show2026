<div>
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
                            {{ $trophy->name }}
                            @if ($trophy->description)
                                <div class="text-sm font-normal text-zinc-500">{{ $trophy->description }}</div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:field>
                                <flux:input
                                    type="text"
                                    wire:model="winningEntries.{{ $trophy->id }}"
                                    wire:blur="saveTrophy({{ $trophy->id }})"
                                    placeholder="e.g. 042"
                                    size="sm"
                                />
                                <flux:error name="winningEntries.{{ $trophy->id }}" />
                            </flux:field>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>
