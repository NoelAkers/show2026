<?php

test('shows registration opening soon text and no link when self entry is not yet open and show is not live', function () {
    config(['show.self_entry_open' => false, 'show.show_live' => false]);

    $response = $this->get(route('public.exhibitor-details'));

    $response->assertSee('Online pre-registration is not yet open');
    $response->assertDontSee('create an account and submit your entries online');
});

test('shows registration link when self entry is open and show is not live', function () {
    config(['show.self_entry_open' => true, 'show.show_live' => false]);

    $response = $this->get(route('public.exhibitor-details'));

    $response->assertSee('create an account and submit your entries online');
});

test('shows too late text and no link once the show is live, regardless of self entry state', function () {
    config(['show.self_entry_open' => true, 'show.show_live' => true]);

    $response = $this->get(route('public.exhibitor-details'));

    $response->assertSee('too late to pre-register');
    $response->assertDontSee('create an account and submit your entries online');
});
