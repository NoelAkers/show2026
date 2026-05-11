<?php

use App\Models\ShowClass;
use App\Models\ShowSection;

test('public schedule is accessible without login', function () {
    $this->get(route('public.schedule'))->assertSuccessful();
});

test('sections are listed in sort_order asc', function () {
    ShowSection::factory()->create(['name' => 'Section B', 'sort_order' => 2]);
    ShowSection::factory()->create(['name' => 'Section A', 'sort_order' => 1]);

    $response = $this->get(route('public.schedule'));

    $response->assertSeeInOrder(['Section A', 'Section B']);
});

test('classes are listed within their section in sort_order asc', function () {
    $section = ShowSection::factory()->create(['sort_order' => 1]);
    ShowClass::factory()->for($section)->create(['name' => 'Class B', 'sort_order' => 2]);
    ShowClass::factory()->for($section)->create(['name' => 'Class A', 'sort_order' => 1]);

    $response = $this->get(route('public.schedule'));

    $response->assertSeeInOrder(['Class A', 'Class B']);
});

test('no exhibitor contact details are exposed in the response', function () {
    $section = ShowSection::factory()->create(['sort_order' => 1]);
    ShowClass::factory()->for($section)->create(['sort_order' => 1]);

    $response = $this->get(route('public.schedule'));

    $response->assertDontSee('email');
    $response->assertDontSee('phone');
    $response->assertDontSee('address');
});

test('no payment status information is exposed', function () {
    $section = ShowSection::factory()->create(['sort_order' => 1]);
    ShowClass::factory()->for($section)->create(['sort_order' => 1]);

    $response = $this->get(route('public.schedule'));

    $response->assertDontSee('has_paid');
    $response->assertDontSee('amount_paid');
});
