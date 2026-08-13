<div>
    @if ($trophies->isEmpty())
        <p class="text-sm text-zinc-500">No trophies yet.</p>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Trophy</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Current Leader(s)</flux:table.column>
                <flux:table.column>Winning Entry</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($trophies as $trophy)
                    <flux:table.row :key="$trophy->id">
                        <flux:table.cell variant="strong">
                            <div class="max-w-[20rem] truncate" title="{{ $trophy->name }}">{{ $trophy->name }}</div>
                            @if ($trophy->description)
                                <div class="max-w-[20rem] truncate text-sm font-normal text-zinc-500" title="{{ $trophy->description }}">{{ $trophy->description }}</div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($trophy->is_points_based)
                                <flux:badge color="blue">Points</flux:badge>
                            @else
                                <flux:badge color="purple">Judge</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($trophy->is_points_based)
                                @php $winners = $trophy->winners(); @endphp
                                <a href="{{ route('admin.trophies.leaderboard', $trophy) }}" class="hover:underline" wire:navigate>
                                    @if ($winners->isEmpty())
                                        <span class="text-zinc-400">No winner yet</span>
                                    @else
                                        {{ $winners->pluck('exhibitor')->pluck('full_name')->join(', ') }}
                                    @endif
                                </a>
                            @else
                                @if ($trophy->winningEntry)
                                    {{ $trophy->winningEntry->exhibitor->full_name }}
                                @else
                                    <span class="text-zinc-400">No winner yet</span>
                                @endif
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($trophy->is_points_based)
                                <span class="text-zinc-400">—</span>
                            @else
                                <div class="sm:w-32">
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
                                </div>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>
