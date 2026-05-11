@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <a href="{{ route('public.schedule') }}" class="mr-4 text-sm font-semibold text-zinc-900 dark:text-white" wire:navigate>
                {{ config('app.name') }}
            </a>

            <flux:navbar class="-mb-px">
                <flux:navbar.item :href="route('public.schedule')" :current="request()->routeIs('public.schedule')" wire:navigate>Schedule</flux:navbar.item>
                <flux:navbar.item :href="route('public.results')" :current="request()->routeIs('public.results')" wire:navigate>Results</flux:navbar.item>
                <flux:navbar.item :href="route('public.trophies')" :current="request()->routeIs('public.trophies')" wire:navigate>Trophies</flux:navbar.item>
            </flux:navbar>
        </flux:header>

        <flux:main container>
            {{ $slot }}
        </flux:main>

        @fluxScripts
    </body>
</html>
