<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => '2026 Calverley Show'])
    </head>
    <body class="min-h-screen bg-white antialiased">
        <div class="flex min-h-screen flex-col">
            <header class="flex items-center justify-end gap-3 p-6">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-md border border-zinc-300 px-4 py-1.5 text-sm text-zinc-800 transition-colors hover:bg-zinc-50">
                        Dashboard
                    </a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="rounded-md px-4 py-1.5 text-sm text-zinc-700 transition-colors hover:text-zinc-900">
                            Log in
                        </a>
                    @endif
                @endauth
            </header>

            <img src="/images/header.png" alt="" class="w-full">

            <main class="flex flex-1 flex-col items-center justify-center gap-10 px-6 py-12">
                <div class="text-center">
                    <h1 class="text-3xl font-semibold tracking-tight text-zinc-900">Calverley Show</h1>
                    <h2 class="text-xl font-semibold tracking-tight text-zinc-900">Calverley Methodist Hall and Church</h2>
                </div>

                <div class="text-center">
                    <h2 class="text-2xl font-semibold tracking-tight text-zinc-900">Saturday 15 August 2026</h2>
                    {{-- <h2 class="text-xl font-semibold tracking-tight text-zinc-900">Doors open 1.00 pm</h2> --}}
                </div>

                <div class="max-w-2xl px-8 py-7 text-center">
                    <p class="leading-relaxed text-zinc-700">
                        Our 105th show was very successful, even the weather cooperated! Many thanks to the exhibitors who put in so much effort to make it a full show and to the Methodist Church for the use of their facilities; 
                        the art display in the church was particularly impressive. Thanks also to the many volunteers who set up and closed the event, provided stewarding, refreshments and ran the always 
                        popular tombola. We were also delighted to welcome back Drighlington Brass Band and a variety of stall holders.
                    </p>
                    <p class="mt-4 leading-relaxed text-zinc-700">
                        Results are available <a href="{{ route('public.results') }}" class="text-zinc-900 underline underline-offset-2 transition-colors hover:text-zinc-600">here</a>.
                    </p>
                    <p class="mt-4 leading-relaxed text-zinc-700">
                        Trophy winners are listed <a href="{{ route('public.trophies') }}" class="text-zinc-900 underline underline-offset-2 transition-colors hover:text-zinc-600">here</a>.
                    </p>
                </div>
            </main>
        </div>

        @fluxScripts
    </body>
</html>
