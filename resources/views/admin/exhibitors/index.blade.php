<x-layouts::app :title="__('Exhibitors')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Exhibitors</flux:heading>
            <flux:button :href="route('admin.exhibitors.create')" variant="primary" icon="plus" wire:navigate>
                Add Exhibitor
            </flux:button>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        @if (session('error'))
            <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
        @endif

        <form method="GET" action="{{ route('admin.exhibitors.index') }}" class="flex flex-wrap gap-3">
            <flux:input name="search" value="{{ request('search') }}" placeholder="Search by name…" class="w-full sm:w-64" />

            <flux:select name="type" class="w-full sm:w-36">
                <flux:select.option value="">All types</flux:select.option>
                <flux:select.option value="adult" :selected="request('type') === 'adult'">Adult</flux:select.option>
                <flux:select.option value="junior" :selected="request('type') === 'junior'">Junior</flux:select.option>
            </flux:select>

            <flux:select name="is_resident" class="w-full sm:w-40">
                <flux:select.option value="">All residency</flux:select.option>
                <flux:select.option value="1" :selected="request('is_resident') === '1'">Resident</flux:select.option>
                <flux:select.option value="0" :selected="request('is_resident') === '0'">Non-resident</flux:select.option>
            </flux:select>

            <flux:select name="has_paid" class="w-full sm:w-36">
                <flux:select.option value="">All payment</flux:select.option>
                <flux:select.option value="1" :selected="request('has_paid') === '1'">Paid</flux:select.option>
                <flux:select.option value="0" :selected="request('has_paid') === '0'">Unpaid</flux:select.option>
            </flux:select>

            <flux:button type="submit">Filter</flux:button>

            @if (request()->hasAny(['search', 'type', 'is_resident', 'has_paid']))
                <flux:button :href="route('admin.exhibitors.index')" variant="ghost" wire:navigate>Clear</flux:button>
            @endif
        </form>

        @if ($exhibitors->isEmpty())
            <p class="text-sm text-zinc-500">No exhibitors found.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Resident</flux:table.column>
                    <flux:table.column>Entries</flux:table.column>
                    <flux:table.column>Financials</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($exhibitors as $exhibitor)
                        <flux:table.row :key="$exhibitor->id">
                            <flux:table.cell variant="strong">
                                <a href="{{ route('admin.exhibitors.show', $exhibitor) }}" class="hover:underline" wire:navigate>
                                    {{ $exhibitor->full_name }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell>{{ ucfirst($exhibitor->type) }}</flux:table.cell>
                            <flux:table.cell>{{ $exhibitor->is_resident ? 'Yes' : 'No' }}</flux:table.cell>
                            <flux:table.cell>{{ $exhibitor->totalEntries() }}</flux:table.cell>
                            <flux:table.cell>
                                <dl class="space-y-0.5 text-xs whitespace-nowrap">
                                    <div><dt class="inline text-zinc-500">Received:</dt> £{{ number_format($exhibitor->receivedFromExhibitorPence() / 100, 2) }}</div>
                                    <div><dt class="inline text-zinc-500">Paid out:</dt> £{{ number_format($exhibitor->paidToExhibitorPence() / 100, 2) }}</div>
                                    <div class="font-semibold {{ $exhibitor->balanceDueToExhibitorPence() < 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}">
                                        <dt class="inline font-normal text-zinc-500">Balance:</dt>
                                        @if ($exhibitor->balanceDueToExhibitorPence() < 0)
                                            −£{{ number_format(abs($exhibitor->balanceDueToExhibitorPence()) / 100, 2) }}
                                        @else
                                            £{{ number_format($exhibitor->balanceDueToExhibitorPence() / 100, 2) }}
                                        @endif
                                    </div>
                                </dl>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($exhibitor->balancePence() === 0)
                                    <flux:badge color="green">Settled</flux:badge>
                                @elseif ($exhibitor->balancePence() > 0)
                                    <flux:badge color="amber">Owes £{{ number_format($exhibitor->balancePence() / 100, 2) }}</flux:badge>
                                @else
                                    <flux:badge color="green">Owed £{{ number_format(abs($exhibitor->balancePence()) / 100, 2) }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:button size="sm" variant="primary" :href="route('admin.exhibitors.add-entry', $exhibitor)" wire:navigate>Add Entries</flux:button>
                                    <flux:button size="sm" :href="route('admin.exhibitors.edit', $exhibitor)" wire:navigate>Edit</flux:button>
                                    <form method="POST" action="{{ route('admin.exhibitors.transactions.store', $exhibitor) }}" class="flex flex-wrap gap-2">
                                        @csrf
                                        <flux:button size="sm" type="submit" name="type" value="cash_receipt">Cash in</flux:button>
                                        <flux:button size="sm" type="submit" name="type" value="card_payment">Card in</flux:button>
                                        <flux:button size="sm" type="submit" name="type" value="cash_payment">Pay out</flux:button>
                                    </form>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
