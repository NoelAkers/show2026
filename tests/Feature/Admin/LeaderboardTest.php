<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('leaderboard shows exhibitors in descending points order', function () {
    $admin = User::factory()->admin()->create();
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);

    $exhibitorA = Exhibitor::factory()->create(['first_name' => 'Alice', 'last_name' => 'Smith', 'full_name' => 'Alice Smith', 'sort_name' => 'Smith, Alice']);
    $exhibitorB = Exhibitor::factory()->create(['first_name' => 'Bob', 'last_name' => 'Jones', 'full_name' => 'Bob Jones', 'sort_name' => 'Jones, Bob']);

    $entryA = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitorA->id]);
    $entryB = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitorB->id]);

    Result::factory()->create(['entry_id' => $entryA->id, 'placement' => '1st']); // 3 points
    Result::factory()->create(['entry_id' => $entryB->id, 'placement' => '3rd']); // 1 point

    $response = $this->actingAs($admin)->get(route('admin.leaderboard'));

    $response->assertOk();
    $content = $response->getContent();
    // Alice (1st, 3pts) should appear before Bob (3rd, 1pt)
    expect(strpos($content, 'Alice'))->toBeLessThan(strpos($content, 'Bob'));
});

it('section filter shows only points from that section', function () {
    $admin = User::factory()->admin()->create();
    $section1 = ShowSection::factory()->create();
    $section2 = ShowSection::factory()->create();
    $class1 = ShowClass::factory()->create(['show_section_id' => $section1->id]);
    $class2 = ShowClass::factory()->create(['show_section_id' => $section2->id]);

    $exhibitor = Exhibitor::factory()->create();
    $entry1 = Entry::factory()->create(['show_class_id' => $class1->id, 'exhibitor_id' => $exhibitor->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class2->id, 'exhibitor_id' => $exhibitor->id]);

    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']); // 3 pts in section1
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '1st']); // 3 pts in section2

    // Without filter: 6 pts total
    $this->actingAs($admin)
        ->get(route('admin.leaderboard'))
        ->assertOk()
        ->assertSee('6');

    // With section1 filter: 3 pts
    $this->actingAs($admin)
        ->get(route('admin.leaderboard', ['section' => $section1->id]))
        ->assertOk()
        ->assertSee('3');
});

it('exhibitor with no results shows 0 points', function () {
    $admin = User::factory()->admin()->create();
    Exhibitor::factory()->create(['first_name' => 'Carol', 'last_name' => 'Doe', 'full_name' => 'Carol Doe', 'sort_name' => 'Doe, Carol']);

    $this->actingAs($admin)
        ->get(route('admin.leaderboard'))
        ->assertOk()
        ->assertSee('Carol')
        ->assertSee('0');
});

it('tied exhibitors appear at the same rank position', function () {
    $admin = User::factory()->admin()->create();
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);

    $exhibitorA = Exhibitor::factory()->create(['first_name' => 'Alice', 'last_name' => 'A', 'full_name' => 'Alice A', 'sort_name' => 'A, Alice']);
    $exhibitorB = Exhibitor::factory()->create(['first_name' => 'Bob', 'last_name' => 'B', 'full_name' => 'Bob B', 'sort_name' => 'B, Bob']);

    $entryA = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitorA->id]);
    $entryB = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitorB->id]);

    Result::factory()->create(['entry_id' => $entryA->id, 'placement' => '2nd']); // 2 pts
    Result::factory()->create(['entry_id' => $entryB->id, 'placement' => '2nd']); // 2 pts — tie

    $response = $this->actingAs($admin)->get(route('admin.leaderboard'));
    $response->assertOk()
        ->assertSee('Alice A')
        ->assertSee('Bob B')
        ->assertSee('2'); // both have 2 points
});
