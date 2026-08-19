<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-6">
        <flux:heading size="xl">Dashboard</flux:heading>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Sections --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Sections</flux:text>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $sectionCount }}</p>
            </div>

            {{-- Classes --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Classes</flux:text>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $classCount }}</p>
                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $classesWithEntries }} with entries · {{ $classesAwaitingJudging }} awaiting judging</flux:text>
            </div>

            {{-- Exhibitors --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Exhibitors</flux:text>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $adultCount + $juniorCount }}</p>
                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $adultCount }} adult · {{ $juniorCount }} junior</flux:text>
                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $paidCount }} paid · {{ $unpaidCount }} unpaid</flux:text>
                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">£{{ number_format($totalReceivedPence / 100, 2) }} received · £{{ number_format($totalDuePence / 100, 2) }} due</flux:text>
            </div>

            {{-- Entries --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Entries</flux:text>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $entryCount }}</p>
            </div>

            {{-- Results --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Results</flux:text>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $resultsEntered }}</p>
                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $resultsOutstanding }} outstanding</flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
