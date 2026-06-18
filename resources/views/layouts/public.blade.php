@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <a href="{{ route('home') }}" class="mr-4 text-sm font-semibold text-zinc-900 dark:text-white" wire:navigate>
                {{ config('app.name') }}
            </a>

            <flux:navbar class="-mb-px">
                <flux:navbar.item :href="route('public.show-details')" :current="request()->routeIs('public.show-details')" wire:navigate>Show Details</flux:navbar.item>
            </flux:navbar>
        </flux:header>

        <flux:main container>
            {{ $slot }}
        </flux:main>

        @fluxScripts
    </body>
</html>
