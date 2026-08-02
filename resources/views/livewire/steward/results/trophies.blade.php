<div>
    @if ($trophies->isEmpty())
        <p class="text-sm text-zinc-500">No trophies assigned. Please contact an administrator.</p>
    @else
        <div class="flex flex-col gap-2">
            @foreach ($trophies as $trophy)
                <div
                    class="flex flex-col gap-2 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between sm:gap-4"
                    wire:key="trophy-{{ $trophy->id }}"
                >
                    <div>
                        <div class="font-medium text-zinc-800 dark:text-zinc-100">{{ $trophy->name }}</div>
                        @if ($trophy->description)
                            <div class="text-sm text-zinc-500">{{ $trophy->description }}</div>
                        @endif
                    </div>
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
                </div>
            @endforeach
        </div>
    @endif
</div>
