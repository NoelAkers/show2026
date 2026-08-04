<x-layouts::public :title="__('For Exhibitors')">
    <div class="prose max-w-none">
        <main>
            <div>
                <h2>Calverley Show</h2>
                <h3>Calverley Methodist Hall and Church</h3>
                <h3>Saturday 15 August 2026</h3>
            </div>
            <div>
                <h2>For Exhibitors</h2>
                <p>
                    We want to encourage entries from as many people as possible, you do not have to be a Calverley Horticultural Society (CHS) member.
                </p>
                <h3>How to enter</h3>
                <p>
                    Details of the classes are in the printed schedule or click the link in the menu. A downloadable PDF copy of the show schedule is available <a href="{{ asset('files/CalverleyHS26.pdf') }}" rel="noopener noreferrer" target="_blank">here</a>. 
                    Adults pay 50p per entry up to 10 entries, additional entries are free.
                    For juniors (up to age 15), entry is free. We can take payment in cash or by card.
                </p>
                <p>
                    Entries can be brought to the Methodist Hall on Friday 14 August 2026 between 6.00 pm and 8.00 pm (preferred), or on Saturday 15 August 2026
                    between 8.00 am and 9.30 am. Judging will start promptly at 10.00 am, please allow adequate time for registration and staging if you are entering on Saturday.
                    <em><strong>Please note that the hall will be closed to exhibitors and visitors during judging.</strong></em>
                </p>
                <h3>Registration</h3>
                <p>
                    All entries can be made with or without prior registration. You need to provide your full name and say whether you are an adult or junior.
                </p>
                <p>
                    Some trophies are restricted to entrants who are residents of Calverley, or have an allotment plot in the village and some are only open to novices, i.e. exhibitors who
                    have not previously won a trophy at this or similar shows, so we will also need this information at the time of registration.
                </p>
                <p>
                    You also need to say how many entries you are making in each class and will receive a uniquely numbered printed label for each entry, which you need to attach to your entry.
                    Paper plates and vases will be provided for your entries where required. Please see the schedule for the (very few) classes where you need to provide a container.
                </p>
                <p>
                    Our stewards will be on hand to help you stage your entries, and to answer any questions you may have about the show. If you need to change or add to your entries,
                    this can be done any time up to 10.00 am Saturday.
                </p>
                <h3>Pre-registration</h3>
                @if (config('show.show_live'))
                    <p>
                        It is now too late to pre-register. Please enter, or change your existing entries, in person when you arrive at the show.
                    </p>
                @elseif (config('show.self_entry_open'))
                    <p>
                        If you would like to save time by registering in advance, <a href="{{ route('register') }}">click here</a>
                        to create an account and submit your entries online up to midday Friday before the show. After that time, if you want to make changes to your entries you can do so when you
                        arrive for staging in the same way as those who have not registered in advance.
                    </p>
                @else
                    <p>
                        Online pre-registration is not yet open, please check back later.
                    </p>
                @endif
                <h3>Art Classes 73-77</h3>
                <p>
                    Art entries will be displayed in the church and <em><strong>staged by our stewards</strong></em>.
                </p>
                <h3>Photography Classes 78, 79, 83</h3>
                <p>
                    Photos should have been taken after August 2025 and be <em><strong>emailed in jpeg format</strong></em> (no pngs)to: <a href="mailto:photos@villageshow.org">photos@villageshow.org</a> by noon on August 14th.
                    Entry fees should be paid at the show, children’s entries are free. Photo entries will be displayed on-screen in the schoolroom.
                </p>
                <h3>Children’s Classes 84-86</h3>
                <p>
                    A template for class 84 (colouring in) can be collected from Calverley Children's Library. Completed entries can be returned to the library <em>before</em> Friday 14th or brought to the show
                     during either the Friday evening or Saturday morning staging times. 
                </p>
                <h3>Trophies, prize money and collection of entries</h3>
                <p>
                    Trophy winners  will be announced and trophies awarded around 3.30 pm. after which prize money can be collected from the show secretary.
                </p>
                <p>
                    If you wish to make any of your entries available for sale to visitors, (Vegetables, Fruit, Flowers, Baking, Preserves only), please place a red sticker (available at the show) on that entry's label.
                    The stewards will move these into the schoolroom for the post-show sale, all proceeds to Calverley Horticultural Society to help cover show costs.
                    <em><strong>Any other items will need to be collected by exhibitors promptly after the show closes at 4.30 pm</strong></em>. The show organisers will be very busy with clearing up after
                    the show, so please be considerate and collect your entries promptly. <em><strong>We cannot be held responsible for any items left after 5.30 pm.</strong></em>
                </p>
            </div>
        </main>
    </div>
</x-layouts::public>
