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
                    <h1 class="text-5xl font-semibold tracking-tight text-zinc-900">2026 Calverley Show</h1>
                </div>

                <div class="max-w-2xl px-8 py-7 text-center">
                    <p class="leading-relaxed text-zinc-700">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.
                    </p>
                    <p class="mt-4 leading-relaxed text-zinc-700">
                        Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est qui dolorem ipsum quia dolor sit amet consectetur adipisci velit. For more information about classes, trophies, and timings, visit the <a href="{{ route('public.show-details') }}" class="text-zinc-900 underline underline-offset-2 transition-colors hover:text-zinc-600">Show Details</a> page.
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
