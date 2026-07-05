@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <a href="{{ route('home') }}" class="mr-4 text-sm font-semibold text-zinc-900 dark:text-white" wire:navigate>
                {{ config('app.name') }}
            </a>

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item :href="route('public.visitor-details')" :current="request()->routeIs('public.visitor-details')" wire:navigate>For Visitors</flux:navbar.item>
                <flux:navbar.item :href="route('public.exhibitor-details')" :current="request()->routeIs('public.exhibitor-details')" wire:navigate>For Exhibitors</flux:navbar.item>
                <flux:navbar.item :href="route('public.schedule')" :current="request()->routeIs('public.schedule')" wire:navigate>Schedule</flux:navbar.item>
                <flux:navbar.item :href="route('public.results')" :current="request()->routeIs('public.results')" wire:navigate>Results</flux:navbar.item>
                <flux:navbar.item :href="route('public.trophies')" :current="request()->routeIs('public.trophies')" wire:navigate>Trophies</flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="max-lg:hidden">
                @auth
                    <flux:navbar.item :href="route('dashboard')" wire:navigate>Dashboard</flux:navbar.item>
                @else
                    <flux:navbar.item :href="route('login')" wire:navigate>Log in</flux:navbar.item>
                    <flux:navbar.item :href="route('register')" wire:navigate>Register</flux:navbar.item>
                @endauth
            </flux:navbar>
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <a href="{{ route('home') }}" class="text-sm font-semibold text-zinc-900 dark:text-white" wire:navigate>
                    {{ config('app.name') }}
                </a>
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item :href="route('public.visitor-details')" :current="request()->routeIs('public.visitor-details')" wire:navigate>For Visitors</flux:sidebar.item>
                <flux:sidebar.item :href="route('public.exhibitor-details')" :current="request()->routeIs('public.exhibitor-details')" wire:navigate>For Exhibitors</flux:sidebar.item>
                <flux:sidebar.item :href="route('public.schedule')" :current="request()->routeIs('public.schedule')" wire:navigate>Schedule</flux:sidebar.item>
                <flux:sidebar.item :href="route('public.results')" :current="request()->routeIs('public.results')" wire:navigate>Results</flux:sidebar.item>
                <flux:sidebar.item :href="route('public.trophies')" :current="request()->routeIs('public.trophies')" wire:navigate>Trophies</flux:sidebar.item>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                @auth
                    <flux:sidebar.item :href="route('dashboard')" wire:navigate>Dashboard</flux:sidebar.item>
                @else
                    <flux:sidebar.item :href="route('login')" wire:navigate>Log in</flux:sidebar.item>
                    <flux:sidebar.item :href="route('register')" wire:navigate>Register</flux:sidebar.item>
                @endauth
            </flux:sidebar.nav>
        </flux:sidebar>

        <flux:main container>
            {{ $slot }}
        </flux:main>

        @fluxScripts
    </body>
</html>
