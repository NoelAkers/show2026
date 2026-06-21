<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('guest is redirected to login', function () {
    $this->get(route('admin.paper-backup'))->assertRedirect(route('login'));
});

it('non-admin receives 403', function () {
    $this->actingAs(User::factory()->steward()->create())
        ->get(route('admin.paper-backup'))
        ->assertForbidden();
});

it('admin can access with no user selected', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.paper-backup'))
        ->assertOk();
});

it('dropdown lists judges and stewards but not exhibitors', function () {
    $judge = User::factory()->judge()->create(['name' => 'Judge Jones']);
    $steward = User::factory()->steward()->create(['name' => 'Steward Smith']);
    User::factory()->create(['name' => 'Plain User']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.paper-backup'))
        ->assertSee('Judge Jones')
        ->assertSee('Steward Smith')
        ->assertDontSee('Plain User');
});

it('shows section and class names for selected user', function () {
    $judge = User::factory()->judge()->create();
    $section = ShowSection::factory()->create(['name' => 'Poultry']);
    $judge->assignedSections()->attach($section);
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Best Hen']);
    Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.paper-backup', ['user_id' => $judge->id]))
        ->assertOk()
        ->assertSee('Poultry')
        ->assertSee('Best Hen');
});

it('does not show exhibitor names', function () {
    $steward = User::factory()->steward()->create();
    $section = ShowSection::factory()->create();
    $steward->assignedSections()->attach($section);
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Alice Example']);
    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.paper-backup', ['user_id' => $steward->id]))
        ->assertDontSee('Alice Example');
});

it('omits classes with no entries', function () {
    $judge = User::factory()->judge()->create();
    $section = ShowSection::factory()->create();
    $judge->assignedSections()->attach($section);
    ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Empty Class']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.paper-backup', ['user_id' => $judge->id]))
        ->assertDontSee('Empty Class');
});

it('shows placement column headers', function () {
    $judge = User::factory()->judge()->create();
    $section = ShowSection::factory()->create();
    $judge->assignedSections()->attach($section);
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.paper-backup', ['user_id' => $judge->id]))
        ->assertSee('1st')
        ->assertSee('2nd')
        ->assertSee('3rd')
        ->assertSee('HC');
});

it('shows empty-state when selected user has no sections', function () {
    $judge = User::factory()->judge()->create(['name' => 'No Sections Judge']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.paper-backup', ['user_id' => $judge->id]))
        ->assertSee('No Sections Judge')
        ->assertSee('no sections assigned');
});

it('returns 404 for a user_id that is not a judge or steward', function () {
    $exhibitor = User::factory()->create(); // default role = Exhibitor

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.paper-backup', ['user_id' => $exhibitor->id]))
        ->assertNotFound();
});
