<x-layouts::app :title="$showSection->name">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('steward.sections.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                My Sections
            </flux:button>
            <flux:heading size="xl">{{ $showSection->name }}</flux:heading>
        </div>

        <livewire:steward.results.section :show-section="$showSection" />
    </div>
</x-layouts::app>
