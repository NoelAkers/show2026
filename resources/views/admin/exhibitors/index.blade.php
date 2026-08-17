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
                    <flux:table.column>Balance Owed</flux:table.column>
                    <flux:table.column>Paid</flux:table.column>
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
                            <flux:table.cell class="{{ $exhibitor->balancePence() > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400' }}">
                                @if ($exhibitor->balancePence() < 0)
                                    −£{{ number_format(abs($exhibitor->balancePence()) / 100, 2) }} (refund owed)
                                @else
                                    £{{ number_format($exhibitor->balancePence() / 100, 2) }}
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($exhibitor->has_paid)
                                    <flux:badge color="green">Paid</flux:badge>
                                @else
                                    <flux:badge color="red">Unpaid</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="primary" :href="route('admin.exhibitors.add-entry', $exhibitor)" wire:navigate>Add Entries</flux:button>
                                    <flux:button size="sm" :href="route('admin.exhibitors.edit', $exhibitor)" wire:navigate>Edit</flux:button>
                                    @if ($exhibitor->has_paid)
                                        <form method="POST" action="{{ route('admin.exhibitors.mark-unpaid', $exhibitor) }}">
                                            @csrf
                                            @method('PATCH')
                                            <flux:button size="sm" variant="ghost" type="submit">Mark Unpaid</flux:button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.exhibitors.mark-paid', $exhibitor) }}">
                                            @csrf
                                            @method('PATCH')
                                            <flux:button size="sm" variant="primary" type="submit">Mark Paid</flux:button>
                                        </form>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
