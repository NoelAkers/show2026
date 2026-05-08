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
                <flux:button :href="route('admin.exhibitors.add-entry', $exhibitor)" variant="primary" wire:navigate>Add Entry</flux:button>
                @if ($exhibitor->entries->isNotEmpty())
                    <flux:button :href="route('admin.exhibitors.labels', $exhibitor)" target="_blank" icon="printer">Print Labels</flux:button>
                @endif
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
                        <dt class="font-medium text-zinc-500">Novice</dt>
                        <dd>{{ $exhibitor->is_novice ? 'Yes' : 'No' }}</dd>
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

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="lg" class="mb-3">Entries</flux:heading>

                @if ($exhibitor->entries->isEmpty())
                    <p class="text-sm text-zinc-500">No entries yet.</p>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>#</flux:table.column>
                            <flux:table.column>Section</flux:table.column>
                            <flux:table.column>Class</flux:table.column>
                            <flux:table.column>Placement</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($exhibitor->entries as $entry)
                                <flux:table.row :key="$entry->id">
                                    <flux:table.cell class="tabular-nums">{{ $entry->entry_number }}</flux:table.cell>
                                    <flux:table.cell>{{ $entry->showClass->showSection->name }}</flux:table.cell>
                                    <flux:table.cell variant="strong">
                                        <a href="{{ route('admin.show-sections.show-classes.show', [$entry->showClass->showSection, $entry->showClass]) }}" class="hover:underline" wire:navigate>
                                            {{ $entry->showClass->name }}
                                        </a>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if ($entry->result)
                                            <flux:badge color="green">{{ $entry->result->placementLabel() }}</flux:badge>
                                        @else
                                            <flux:badge color="zinc">Pending</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
