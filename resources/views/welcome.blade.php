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
                    <h2 class="text-2xl font-semibold tracking-tight text-zinc-900">Saturday 14 August 2026</h2>
                    <h2 class="text-xl font-semibold tracking-tight text-zinc-900">Doors open 1.00 pm</h2>
                </div>

                <div class="max-w-2xl px-8 py-7 text-center">
                    <p class="leading-relaxed text-zinc-700">
                        We’re looking forward to welcoming you to our 105th show this year, we’re hoping it will be bigger and better than ever. We have our traditional vegetable, fruit and flower classes displayed in the main hall. Baking and junior classes will be in the school room and for the first time, we’ll be displaying the art in the church. As always we’ll have refreshments, stalls and a tombola, we’re also delighted to welcome back Drighlington Brass Band, who were a big hit last year.
                    </p>
                    <p class="mt-4 leading-relaxed text-zinc-700">
                        For more information about the classes, trophies, timings for visitors and exhibitors and how to enter, click here: <a href="{{ route('public.show-details') }}" class="text-zinc-900 underline underline-offset-2 transition-colors hover:text-zinc-600">Show Details</a>.
                    </p>
                    <p class="mt-4 text-sm text-zinc-500">
                        If you would like to enter classes in advance of the show, you can <a href="{{ route('register') }}" class="text-zinc-700 underline underline-offset-2 transition-colors hover:text-zinc-900">register here</a> to create an account and submit your entries online.
                    </p>
                </div>
            </main>
        </div>

        @fluxScripts
    </body>
</html>
