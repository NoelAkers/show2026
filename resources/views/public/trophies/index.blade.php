<x-layouts::public :title="__('Trophies')">
    <div class="flex flex-col gap-4">
        <flux:heading size="xl">Trophies</flux:heading>

        @forelse ($trophies as $trophy)
            @php $winners = $trophy->winners(); @endphp
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="lg">{{ $trophy->name }}</flux:heading>

                @if ($trophy->description)
                    <flux:text class="mt-1 mb-3">{{ $trophy->description }}</flux:text>
                @endif

                <div class="mt-2">
                    @if ($winners->isEmpty())
                        <flux:text class="text-zinc-500 italic">To be announced.</flux:text>
                    @else
                        <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            {{ $winners->count() > 1 ? 'Winners' : 'Winner' }}:
                        </flux:text>
                        <ul class="mt-1 space-y-0.5">
                            @foreach ($winners as $winner)
                                <li class="font-semibold">{{ $winner['exhibitor']->full_name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @empty
            <flux:text class="text-zinc-500">No trophies have been configured yet.</flux:text>
        @endforelse
    </div>
</x-layouts::public>
