<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => '2026 Calverley Show'])
    </head>
    <body class="min-h-screen antialiased">
        <div class="relative min-h-screen flex flex-col">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/images/VEG_BACKGROUND.jpg')"></div>
            <div class="absolute inset-0 bg-black/55"></div>

            <div class="relative z-10 flex min-h-screen flex-col">
                <header class="flex items-center justify-end gap-3 p-6">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md border border-white/40 bg-white/10 px-4 py-1.5 text-sm text-white backdrop-blur-sm transition-colors hover:bg-white/20">
                            Dashboard
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="rounded-md px-4 py-1.5 text-sm text-white/80 transition-colors hover:text-white">
                                Log in
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-md border border-white/40 bg-white/10 px-4 py-1.5 text-sm text-white backdrop-blur-sm transition-colors hover:bg-white/20">
                                Register
                            </a>
                        @endif
                    @endauth
                </header>

                <main class="flex flex-1 flex-col items-center justify-center gap-10 px-6 py-12">
                    <div class="text-center">
                        <h1 class="text-5xl font-semibold tracking-tight text-white drop-shadow-lg">2026 Calverley Show</h1>
                    </div>

                    <nav class="flex flex-wrap justify-center gap-4">
                        <a href="{{ route('public.schedule') }}" class="flex min-w-40 flex-col items-center gap-1 rounded-xl border border-white/25 bg-white/15 px-8 py-5 text-white backdrop-blur-sm transition-colors hover:bg-white/25">
                            <span class="text-lg font-semibold">Schedule</span>
                            <span class="text-sm text-white/70">Classes &amp; timings</span>
                        </a>
                        <a href="{{ route('public.results') }}" class="flex min-w-40 flex-col items-center gap-1 rounded-xl border border-white/25 bg-white/15 px-8 py-5 text-white backdrop-blur-sm transition-colors hover:bg-white/25">
                            <span class="text-lg font-semibold">Results</span>
                            <span class="text-sm text-white/70">Placements &amp; awards</span>
                        </a>
                        <a href="{{ route('public.trophies') }}" class="flex min-w-40 flex-col items-center gap-1 rounded-xl border border-white/25 bg-white/15 px-8 py-5 text-white backdrop-blur-sm transition-colors hover:bg-white/25">
                            <span class="text-lg font-semibold">Trophies</span>
                            <span class="text-sm text-white/70">Trophy winners</span>
                        </a>
                    </nav>
                </main>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
