<x-layouts::app :title="$trophy->name">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('judge.trophies.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                My Trophies
            </flux:button>
            <flux:heading size="xl">{{ $trophy->name }}</flux:heading>
        </div>

        @if ($trophy->description)
            <p class="text-sm text-zinc-500">{{ $trophy->description }}</p>
        @endif

        <livewire:judge.results.trophy :trophy="$trophy" />
    </div>
</x-layouts::app>
