<x-layouts::app :title="__('Account Pending')">
    <div class="flex h-full w-full flex-1 flex-col items-center justify-center gap-4 p-4">
        <flux:heading size="xl">Account Pending</flux:heading>
        <p class="text-sm text-zinc-500 max-w-md text-center">
            Your account has been registered but has not yet been activated.
            Please contact the show organisers to have your account set up.
        </p>
        <flux:button :href="route('home')" variant="ghost" wire:navigate>Return to home</flux:button>
    </div>
</x-layouts::app>
