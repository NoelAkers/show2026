<?php

use App\Models\Entry;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function resultAdmin(): User
{
    return User::factory()->admin()->create();
}

it('admin can enter a result for any class in any section', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs(resultAdmin())
        ->post(route('admin.show-sections.show-classes.results.store', [$section, $class]), [
            'entry_id' => $entry->id,
            'placement' => '1st',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('results', [
        'entry_id' => $entry->id,
        'placement' => '1st',
    ]);
});

it('admin can update any result', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);
    $result = Result::factory()->create(['entry_id' => $entry->id, 'placement' => '3rd']);

    $this->actingAs(resultAdmin())
        ->patch(route('admin.show-sections.show-classes.results.update', [$section, $class, $result]), [
            'placement' => '1st',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('results', ['id' => $result->id, 'placement' => '1st']);
});

it('admin cannot create a duplicate 1st in a class', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']);

    $this->actingAs(resultAdmin())
        ->post(route('admin.show-sections.show-classes.results.store', [$section, $class]), [
            'entry_id' => $entry2->id,
            'placement' => '1st',
        ])
        ->assertSessionHasErrors('placement');
});

it('admin cannot create a duplicate 2nd or 3rd in a class', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '2nd']);

    $this->actingAs(resultAdmin())
        ->post(route('admin.show-sections.show-classes.results.store', [$section, $class]), [
            'entry_id' => $entry2->id,
            'placement' => '2nd',
        ])
        ->assertSessionHasErrors('placement');
});

it('entered_by_user_id is set to the admin user id', function () {
    $admin = resultAdmin();
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($admin)
        ->post(route('admin.show-sections.show-classes.results.store', [$section, $class]), [
            'entry_id' => $entry->id,
            'placement' => '2nd',
        ]);

    $this->assertDatabaseHas('results', [
        'entry_id' => $entry->id,
        'entered_by_user_id' => $admin->id,
    ]);
});

it('judge receives 403 when accessing admin result routes', function () {
    $judge = User::factory()->judge()->create();
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge)
        ->post(route('admin.show-sections.show-classes.results.store', [$section, $class]), [
            'entry_id' => $entry->id,
            'placement' => '1st',
        ])
        ->assertForbidden();
});
