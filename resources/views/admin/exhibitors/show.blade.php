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
                @if ($exhibitor->entries->contains(fn ($entry) => $entry->result?->placement))
                    <flux:button :href="route('admin.result-cards', ['filter' => 'all', 'exhibitor_id' => $exhibitor->id])" target="_blank" icon="printer">Print Result Cards</flux:button>
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
                        <dt class="font-medium text-zinc-500">Fee Owed</dt>
                        <dd class="font-semibold">£{{ number_format($exhibitor->feeOwedPence() / 100, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-500">Winnings</dt>
                        <dd class="font-semibold text-green-600 dark:text-green-400">
                            £{{ number_format($exhibitor->winningsPence() / 100, 2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-500">Amount Paid</dt>
                        <dd class="font-semibold">£{{ number_format($exhibitor->amountPaidPence() / 100, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-500">Balance</dt>
                        <dd class="font-semibold {{ $exhibitor->balancePence() > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}">
                            @if ($exhibitor->balancePence() < 0)
                                −£{{ number_format(abs($exhibitor->balancePence()) / 100, 2) }} (refund owed)
                            @else
                                £{{ number_format($exhibitor->balancePence() / 100, 2) }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-500">Payment Status</dt>
                        <dd>
                            @if ($exhibitor->hasPaid())
                                <flux:badge color="green">Paid</flux:badge>
                            @else
                                <flux:badge color="red">Unpaid</flux:badge>
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 flex flex-wrap items-end gap-4">
                    <form method="POST" action="{{ route('admin.exhibitors.transactions.store', $exhibitor) }}">
                        @csrf
                        <flux:field>
                            <flux:label>Amount (£)</flux:label>
                            <flux:input type="number" name="amount_pounds" step="0.01" min="0.01" class="w-28" required />
                        </flux:field>
                        <flux:field>
                            <flux:label>Type</flux:label>
                            <flux:select name="type">
                                <flux:select.option value="cash_receipt">Cash receipt</flux:select.option>
                                <flux:select.option value="card_payment">Card payment</flux:select.option>
                                <flux:select.option value="cash_payment">Cash payment (refund/payout)</flux:select.option>
                            </flux:select>
                        </flux:field>
                        <flux:button type="submit">Record Transaction</flux:button>
                    </form>
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="lg" class="mb-3">Transaction History</flux:heading>

                @if ($exhibitor->transactions->isEmpty())
                    <p class="text-sm text-zinc-500">No transactions recorded.</p>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Date</flux:table.column>
                            <flux:table.column>Type</flux:table.column>
                            <flux:table.column>Amount</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($exhibitor->transactions->sortByDesc('created_at') as $transaction)
                                <flux:table.row :key="$transaction->id">
                                    <flux:table.cell>{{ $transaction->created_at->format('d M Y H:i') }}</flux:table.cell>
                                    <flux:table.cell>{{ str($transaction->type->value)->replace('_', ' ')->title() }}</flux:table.cell>
                                    <flux:table.cell>£{{ number_format($transaction->amount_pence / 100, 2) }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </div>
        </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="lg" class="mb-3">Entries</flux:heading>

                <livewire:admin.exhibitors.entries-table :exhibitor="$exhibitor" />
            </div>
        </div>
    </div>
</x-layouts::app>
