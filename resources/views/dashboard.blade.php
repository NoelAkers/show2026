<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-6">
        <flux:heading size="xl">Dashboard</flux:heading>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Sections & Classes --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Sections</flux:text>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $sectionCount }}</p>
                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $classCount }} {{ Str::plural('class', $classCount) }}</flux:text>
            </div>

            {{-- Exhibitors --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Exhibitors</flux:text>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $adultCount + $juniorCount }}</p>
                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $adultCount }} adult · {{ $juniorCount }} junior</flux:text>
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

            {{-- Payment --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Payments (adults)</flux:text>
                <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">{{ $paidCount }}</p>
                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $unpaidCount }} unpaid</flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
