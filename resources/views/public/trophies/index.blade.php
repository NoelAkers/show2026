<x-layouts::public :title="__('Trophies')">
    <div class="flex flex-col gap-6">
        <flux:heading size="xl">Trophies</flux:heading>

        @if ($trophies->isEmpty())
            <flux:text class="text-zinc-500">No trophies have been configured yet.</flux:text>
        @else
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Trophy</flux:table.column>
                        <flux:table.column>Winner</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($trophies as $trophy)
                            @php $winners = $trophy->winners(); @endphp
                            <flux:table.row :key="$trophy->id">
                                <flux:table.cell class="align-top whitespace-normal wrap-break-word">
                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $trophy->name }}</div>
                                    @if ($trophy->description)
                                        <div class="text-sm text-zinc-500">{{ $trophy->description }}</div>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="align-top whitespace-normal wrap-break-word">
                                    @if ($winners->isEmpty())
                                        <span class="text-zinc-500 italic">To be announced.</span>
                                    @else
                                        <span class="font-semibold">{{ $winners->pluck('exhibitor')->pluck('full_name')->join(', ') }}</span>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @endif
    </div>
</x-layouts::public>
