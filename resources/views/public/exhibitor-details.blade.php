<x-layouts::public :title="__('For Exhibitors')">
    <div class="flex min-h-screen flex-col">
        <main class="flex flex-1 flex-col items-center gap-10 px-6 py-12">
            <div class="text-center">
                <h1 class="text-3xl font-semibold tracking-tight text-zinc-900">Calverley Show</h1>
                <h2 class="text-2xl font-semibold tracking-tight text-zinc-900">For Exhibitors</h2>
            </div>
            <div class="w-full max-w-full sm:max-w-3xl px-4 sm:px-8 py-7 text-justify">
                <p class="leading-relaxed text-zinc-700">
                    Calverley Horticultural Society’s 105th show will be held on Saturday 14 August 2026, at the Calverley Methodist Hall and Church. 
                    Doors open at 1.00 pm by which time judging should be finished and you will be able to access the Fruit and Vegetable sections in the main hall, the Art in the church and 
                    Baking, Handicrafts and other sections in the school room. Admission is pay as you feel for all visitors.
                </p>
                <p class="mt-4 leading-relaxed text-zinc-700">
                    There will be live music from the Drighlington Brass Band in the paved area outside the hall, together with food and other stalls. Coffee and cake is available indoors, (as well as limited seating if the weather is unkind), 
                    along with our always popular Tombola stall. 
                </p>
                <p class="mt-4 leading-relaxed text-zinc-700">      
                    Prizes will be announced and awarded around 4.00 pm after which some items from the Baking, Fruit and Vegetable sections will be available in the schoolroom, for a suitable donation . The show will close at 4.30 pm. For more information about the classes, trophies, timings for visitors and exhibitors and how to enter, click here: <a href="{{ route('public.show-details') }}" class="text-zinc-900 underline underline-offset-2 transition-colors hover:text-zinc-600">Show Details</a>.
                </p>
                <p class="mt-4 leading-relaxed text-zinc-700">
                    If you would like to enter classes in advance of the show, you can <a href="{{ route('register') }}" class="text-zinc-700 underline underline-offset-2 transition-colors hover:text-zinc-900">register here</a> to create an account and submit your entries online.
                </p>
            </div>
        </main>
    </div>
</x-layouts::public>
