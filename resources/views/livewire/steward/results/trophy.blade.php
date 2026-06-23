<div class="flex flex-col gap-4">
    @if ($saved)
        <flux:callout variant="success" icon="check-circle">Winner saved successfully.</flux:callout>
    @endif

    <flux:field>
        <flux:label>Winning Entry Number</flux:label>
        <flux:input type="text" wire:model="winningEntryNumber" placeholder="e.g. 042" />
        <flux:error name="winningEntryNumber" />
    </flux:field>

    <div>
        <flux:button variant="primary" wire:click="save">Save</flux:button>
    </div>
</div>
