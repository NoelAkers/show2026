<x-layouts::app :title="__('My Exhibitor Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">My Show Entries</flux:heading>
            <flux:button :href="route('exhibitor.profile.edit')" variant="ghost" icon="pencil" wire:navigate>
                Edit Profile
            </flux:button>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        @if (session('info'))
            <flux:callout variant="info" icon="information-circle">{{ session('info') }}</flux:callout>
        @endif

        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700 max-w-2xl">
            <flux:heading size="lg" class="mb-3">Your Details</flux:heading>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="font-medium text-zinc-500">Name</dt>
                    <dd>{{ $exhibitor->full_name }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-zinc-500">Type</dt>
                    <dd>{{ ucfirst($exhibitor->type) }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-zinc-500">Village Resident</dt>
                    <dd>{{ $exhibitor->is_resident ? 'Yes' : 'No' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-zinc-500">Novice</dt>
                    <dd>{{ $exhibitor->is_novice ? 'Yes' : 'No' }}</dd>
                </div>
            </dl>
        </div>

        <livewire:admin.exhibitors.entry-manager :exhibitor="$exhibitor" />
    </div>
</x-layouts::app>
