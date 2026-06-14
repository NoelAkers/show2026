<x-layouts::app :title="__('Self-Entry Closed')">
    <div class="flex h-full w-full flex-1 flex-col items-center justify-center gap-4 p-4">
        <flux:heading size="xl">Self-Entry is Currently Closed</flux:heading>
        <p class="text-sm text-zinc-500 max-w-md text-center">
            Online self-entry is not open at this time.
            Please contact the show organisers if you need assistance.
        </p>
        <flux:button :href="route('home')" variant="ghost" wire:navigate>Return to home</flux:button>
    </div>
</x-layouts::app>
