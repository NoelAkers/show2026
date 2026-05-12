<div class="flex flex-col gap-4">
    @if (session('success'))
        <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
    @endif

    @if ($errors->any())
        <flux:callout variant="danger" icon="x-circle">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </flux:callout>
    @endif

    @if ($showClass->entries->isEmpty())
        <p class="text-sm text-zinc-500">No entries in this class.</p>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Placement</flux:table.column>
                <flux:table.column>Notes</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($showClass->entries as $entry)
                    <flux:table.row :key="$entry->id">
                        <flux:table.cell class="tabular-nums">{{ $entry->entry_number }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-4">
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
                            <flux:input wire:model="notes.{{ $entry->id }}" placeholder="Notes…" size="sm" class="w-48" />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        <div>
            <flux:button wire:click="save" variant="primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Save Results</span>
                <span wire:loading wire:target="save">Saving…</span>
            </flux:button>
        </div>
    @endif
</div>
