<x-layouts::app :title="$showClass->name">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center gap-2">
            <flux:button :href="route('judge.sections.show', $showSection)" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
                {{ $showSection->name }}
            </flux:button>
            <flux:heading size="xl">{{ $showClass->name }}</flux:heading>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        @if ($errors->any())
            <flux:callout variant="danger" icon="x-circle">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </flux:callout>
        @endif

        @if ($showClass->entries->isEmpty())
            <p class="text-sm text-zinc-500">No entries in this class.</p>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>#</flux:table.column>
                    <flux:table.column>Exhibitor</flux:table.column>
                    <flux:table.column>Result</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($showClass->entries as $entry)
                        <flux:table.row :key="$entry->id">
                            <flux:table.cell class="tabular-nums">{{ $entry->entry_number }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ $entry->exhibitor->full_name }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($entry->result)
                                    <form method="POST" action="{{ route('judge.results.update', [$showSection, $showClass, $entry->result]) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <flux:select name="placement" class="w-44">
                                            <flux:select.option value="">No placement</flux:select.option>
                                            <flux:select.option value="1st" :selected="$entry->result->placement === '1st'">1st Place</flux:select.option>
                                            <flux:select.option value="2nd" :selected="$entry->result->placement === '2nd'">2nd Place</flux:select.option>
                                            <flux:select.option value="3rd" :selected="$entry->result->placement === '3rd'">3rd Place</flux:select.option>
                                            <flux:select.option value="highly_commended" :selected="$entry->result->placement === 'highly_commended'">Highly Commended</flux:select.option>
                                        </flux:select>
                                        <flux:input name="notes" value="{{ $entry->result->notes }}" placeholder="Notes…" class="w-48" />
                                        <flux:button size="sm" type="submit">Save</flux:button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('judge.results.store', [$showSection, $showClass]) }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="entry_id" value="{{ $entry->id }}">
                                        <flux:select name="placement" class="w-44">
                                            <flux:select.option value="">No placement</flux:select.option>
                                            <flux:select.option value="1st">1st Place</flux:select.option>
                                            <flux:select.option value="2nd">2nd Place</flux:select.option>
                                            <flux:select.option value="3rd">3rd Place</flux:select.option>
                                            <flux:select.option value="highly_commended">Highly Commended</flux:select.option>
                                        </flux:select>
                                        <flux:input name="notes" placeholder="Notes…" class="w-48" />
                                        <flux:button size="sm" type="submit">Save</flux:button>
                                    </form>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
