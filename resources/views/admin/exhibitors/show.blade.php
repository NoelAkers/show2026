<x-layouts::app :title="$exhibitor->full_name">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <flux:button :href="route('admin.exhibitors.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                    Exhibitors
                </flux:button>
                <flux:heading size="xl">{{ $exhibitor->full_name }}</flux:heading>
            </div>
            <div class="flex gap-2">
                <flux:button :href="route('admin.exhibitors.edit', $exhibitor)" wire:navigate>Edit</flux:button>
                <form method="POST" action="{{ route('admin.exhibitors.destroy', $exhibitor) }}">
                    @csrf
                    @method('DELETE')
                    <flux:button variant="danger" type="submit">Delete</flux:button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        <div class="grid max-w-2xl gap-6">
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="lg" class="mb-3">Details</flux:heading>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="font-medium text-zinc-500">Type</dt>
                        <dd>{{ ucfirst($exhibitor->type) }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-500">Resident</dt>
                        <dd>{{ $exhibitor->is_resident ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-500">Email</dt>
                        <dd>{{ $exhibitor->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-500">Phone</dt>
                        <dd>{{ $exhibitor->phone ?? '—' }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="font-medium text-zinc-500">Address</dt>
                        <dd>{{ $exhibitor->address ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="lg" class="mb-3">Fee Summary</flux:heading>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="font-medium text-zinc-500">Total Entries</dt>
                        <dd>{{ $exhibitor->totalEntries() }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-500">Chargeable Entries</dt>
                        <dd>{{ $exhibitor->chargeableEntries() }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-500">Total Fee Owed</dt>
                        <dd class="font-semibold">£{{ number_format($exhibitor->feeOwedPence() / 100, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-500">Payment Status</dt>
                        <dd>
                            @if ($exhibitor->has_paid)
                                <flux:badge color="green">Paid</flux:badge>
                            @else
                                <flux:badge color="red">Unpaid</flux:badge>
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="mt-4">
                    @if ($exhibitor->has_paid)
                        <form method="POST" action="{{ route('admin.exhibitors.mark-unpaid', $exhibitor) }}">
                            @csrf
                            @method('PATCH')
                            <flux:button variant="ghost" type="submit">Mark as Unpaid</flux:button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.exhibitors.mark-paid', $exhibitor) }}">
                            @csrf
                            @method('PATCH')
                            <flux:button variant="primary" type="submit">Mark as Paid</flux:button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
