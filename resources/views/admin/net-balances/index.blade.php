<x-layouts::app :title="__('Net Balances')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <flux:heading size="xl">Net Balances</flux:heading>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Entry Fees</flux:text>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">£{{ number_format($totalFeePence / 100, 2) }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Prize Money</flux:text>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">£{{ number_format($totalWinningsPence / 100, 2) }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Net Balance</flux:text>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ $totalNetPence < 0 ? '−' : '' }}£{{ number_format(abs($totalNetPence) / 100, 2) }}
                </p>
            </div>
        </div>

        @if ($exhibitors->isEmpty())
            <p class="text-sm text-zinc-500">No exhibitors found.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Entries</flux:table.column>
                    <flux:table.column>Entry Fee</flux:table.column>
                    <flux:table.column>Prize Money</flux:table.column>
                    <flux:table.column>Net Balance</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($exhibitors as $exhibitor)
                        @php $netPence = $exhibitor->winningsPence() - $exhibitor->feeOwedPence(); @endphp
                        <flux:table.row :key="$exhibitor->id">
                            <flux:table.cell variant="strong">{{ $exhibitor->full_name }}</flux:table.cell>
                            <flux:table.cell>{{ ucfirst($exhibitor->type) }}</flux:table.cell>
                            <flux:table.cell>{{ $exhibitor->totalEntries() }}</flux:table.cell>
                            <flux:table.cell>£{{ number_format($exhibitor->feeOwedPence() / 100, 2) }}</flux:table.cell>
                            <flux:table.cell>£{{ number_format($exhibitor->winningsPence() / 100, 2) }}</flux:table.cell>
                            <flux:table.cell class="{{ $netPence < 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ $netPence < 0 ? '−' : '' }}£{{ number_format(abs($netPence) / 100, 2) }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
