<x-layouts::app :title="$showClass->name">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('judge.sections.show', $showSection)" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                {{ $showSection->name }}
            </flux:button>
            <flux:heading size="xl">{{ $showClass->name }}</flux:heading>
        </div>

        <livewire:judge.results.index :show-section="$showSection" :show-class="$showClass" />
    </div>
</x-layouts::app>
