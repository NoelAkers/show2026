<x-layouts::app :title="__('My Trophies')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <flux:heading size="xl">My Trophies</flux:heading>

        <livewire:judge.results.trophies />
    </div>
</x-layouts::app>
