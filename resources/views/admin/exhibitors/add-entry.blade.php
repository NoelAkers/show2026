<x-layouts::app :title="'Add Entry — ' . $exhibitor->full_name">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <livewire:admin.exhibitors.entry-manager :exhibitor="$exhibitor" />
    </div>
</x-layouts::app>
