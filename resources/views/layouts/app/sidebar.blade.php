<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <flux:heading class="px-2 py-1 text-lg font-semibold">{{ __('Menu') }}</flux:heading>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                @if (auth()->user()?->isAdmin())
                    <flux:sidebar.group :heading="__('Pre-show')" class="grid">
                        <flux:sidebar.item icon="banknotes" :href="route('admin.prize-levels.index')" :current="request()->routeIs('admin.prize-levels.*')" wire:navigate>
                            {{ __('Prize Levels') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="rectangle-stack" :href="route('admin.show-sections.index')" :current="request()->routeIs('admin.show-sections.*')" wire:navigate>
                            {{ __('Sections') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="identification" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                            {{ __('Users') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="academic-cap" :href="route('admin.judges.index')" :current="request()->routeIs('admin.judges.*')" wire:navigate>
                            {{ __('Judges') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="shield-check" :href="route('admin.stewards.index')" :current="request()->routeIs('admin.stewards.*')" wire:navigate>
                            {{ __('Stewards') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="gift" :href="route('admin.trophies.index')" :current="request()->routeIs('admin.trophies.*')" wire:navigate>
                            {{ __('Trophies') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Entries')" class="grid">
                        <flux:sidebar.item icon="users" :href="route('admin.exhibitors.index')" :current="request()->routeIs('admin.exhibitors.*')" wire:navigate>
                            {{ __('Exhibitors') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="banknotes" :href="route('admin.net-balances')" :current="request()->routeIs('admin.net-balances')" wire:navigate>
                            {{ __('Net Balances') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Results')" class="grid">
                        <flux:sidebar.item icon="trophy" :href="route('admin.leaderboard')" :current="request()->routeIs('admin.leaderboard')" wire:navigate>
                            {{ __('Leaderboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="printer" :href="route('admin.paper-backup')" :current="request()->routeIs('admin.paper-backup')" wire:navigate>
                            {{ __('Paper Backup') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif (auth()->user()?->isJudge())
                    <flux:sidebar.group :heading="__('My Work')" class="grid">
                        <flux:sidebar.item icon="rectangle-stack" :href="route('judge.sections.index')" :current="request()->routeIs('judge.sections.*')" wire:navigate>
                            {{ __('Sections') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="gift" :href="route('judge.trophies.index')" :current="request()->routeIs('judge.trophies.*')" wire:navigate>
                            {{ __('Trophies') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif (auth()->user()?->isSteward())
                    <flux:sidebar.group :heading="__('My Work')" class="grid">
                        <flux:sidebar.item icon="rectangle-stack" :href="route('steward.sections.index')" :current="request()->routeIs('steward.sections.*')" wire:navigate>
                            {{ __('Sections') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="gift" :href="route('steward.trophies.index')" :current="request()->routeIs('steward.trophies.*')" wire:navigate>
                            {{ __('Trophies') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif (auth()->user()?->isHelper())
                    <flux:sidebar.group :heading="__('Exhibitors')" class="grid">
                        <flux:sidebar.item icon="users" :href="route('helper.exhibitors.index')" :current="request()->routeIs('helper.exhibitors.*')" wire:navigate>
                            {{ __('Exhibitors') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif (auth()->user()?->isExhibitor())
                    <flux:sidebar.group :heading="__('My Show')" class="grid">
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('exhibitor.dashboard')" :current="request()->routeIs('exhibitor.*')" wire:navigate>
                            {{ __('My Entries') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
