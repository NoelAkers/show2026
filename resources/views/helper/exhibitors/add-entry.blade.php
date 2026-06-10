<x-layouts::app :title="'Add Entries — ' . $exhibitor->full_name">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <flux:button variant="ghost" icon="arrow-left" :href="route('helper.exhibitors.index')" wire:navigate>
            Back to Exhibitors
        </flux:button>

        <livewire:admin.exhibitors.entry-manager :exhibitor="$exhibitor" />
    </div>
</x-layouts::app>
