<x-layouts::app :title="__('Leaderboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Leaderboard</flux:heading>
            <div class="flex gap-2">
                <flux:button :href="route('admin.trophy-cards')" variant="ghost" icon="printer">
                    Trophy Cards
                </flux:button>
                <flux:button :href="route('admin.trophy-list')" variant="ghost" icon="printer">
                    Trophy List
                </flux:button>
            </div>
        </div>

        <livewire:admin.leaderboard />
    </div>
</x-layouts::app>
