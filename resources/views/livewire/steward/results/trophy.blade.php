<div class="flex flex-col gap-4">
    @if ($saved)
        <flux:callout variant="success" icon="check-circle">Winner saved successfully.</flux:callout>
    @endif

    @if ($entries->isEmpty())
        <p class="text-sm text-zinc-500">No entries exist yet.</p>
    @else
        <flux:field>
            <flux:label>Winning Entry</flux:label>
            <flux:select wire:model="winningEntryId" variant="listbox" searchable placeholder="Search by entry number or name…" clearable>
                @foreach ($entries as $entry)
                    <flux:select.option value="{{ $entry->id }}">
                        {{ $entry->formatted_entry_number }} — {{ $entry->exhibitor->full_name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>

        <div>
            <flux:button variant="primary" wire:click="save">Save</flux:button>
        </div>
    @endif
</div>
