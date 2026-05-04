<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function judgeWithSection(): array
{
    $judge = User::factory()->judge()->create();
    $section = ShowSection::factory()->create();
    $judge->assignedSections()->attach($section);

    return [$judge, $section];
}

it('judge sees only classes in their assigned sections', function () {
    [$judge, $section] = judgeWithSection();
    ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Roses']);

    $this->actingAs($judge)
        ->get(route('judge.sections.show', $section))
        ->assertOk()
        ->assertSee('Roses');
});

it('judge cannot view classes in an unassigned section', function () {
    [$judge] = judgeWithSection();
    $other = ShowSection::factory()->create();

    $this->actingAs($judge)
        ->get(route('judge.sections.show', $other))
        ->assertForbidden();
});

it('judge can enter a placement and notes for an entry', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge)
        ->post(route('judge.results.store', [$section, $class]), [
            'entry_id' => $entry->id,
            'placement' => '1st',
            'notes' => 'Beautiful arrangement.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('results', [
        'entry_id' => $entry->id,
        'placement' => '1st',
        'notes' => 'Beautiful arrangement.',
        'entered_by_user_id' => $judge->id,
    ]);
});

it('judge cannot assign a second 1st place in the same class', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']);

    $this->actingAs($judge)
        ->post(route('judge.results.store', [$section, $class]), [
            'entry_id' => $entry2->id,
            'placement' => '1st',
        ])
        ->assertSessionHasErrors('placement');
});

it('judge cannot assign a second 2nd place in the same class', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '2nd']);

    $this->actingAs($judge)
        ->post(route('judge.results.store', [$section, $class]), [
            'entry_id' => $entry2->id,
            'placement' => '2nd',
        ])
        ->assertSessionHasErrors('placement');
});

it('judge cannot assign a second 3rd place in the same class', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '3rd']);

    $this->actingAs($judge)
        ->post(route('judge.results.store', [$section, $class]), [
            'entry_id' => $entry2->id,
            'placement' => '3rd',
        ])
        ->assertSessionHasErrors('placement');
});

it('multiple highly commended in one class is allowed', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => 'highly_commended']);

    $this->actingAs($judge)
        ->post(route('judge.results.store', [$section, $class]), [
            'entry_id' => $entry2->id,
            'placement' => 'highly_commended',
        ])
        ->assertRedirect();

    $this->assertDatabaseCount('results', 2);
});

it('judge can clear a placement by setting to null', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);
    $result = Result::factory()->create(['entry_id' => $entry->id, 'placement' => '1st']);

    $this->actingAs($judge)
        ->patch(route('judge.results.update', [$section, $class, $result]), [
            'placement' => '',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('results', [
        'id' => $result->id,
        'placement' => null,
    ]);
});

it('entered result is immediately visible to admin', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge)
        ->post(route('judge.results.store', [$section, $class]), [
            'entry_id' => $entry->id,
            'placement' => '1st',
        ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.show-sections.show-classes.show', [$section, $class]))
        ->assertOk()
        ->assertSee('1st');
});

it('judge can change an existing placement', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);
    $result = Result::factory()->create(['entry_id' => $entry->id, 'placement' => '2nd']);

    $this->actingAs($judge)
        ->patch(route('judge.results.update', [$section, $class, $result]), [
            'placement' => '1st',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('results', ['id' => $result->id, 'placement' => '1st']);
});

it('uniqueness rule is enforced when updating', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']);
    $result2 = Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '2nd']);

    $this->actingAs($judge)
        ->patch(route('judge.results.update', [$section, $class, $result2]), [
            'placement' => '1st',
        ])
        ->assertSessionHasErrors('placement');
});

it('exhibitor points total changes after updating a result', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create();
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    $result = Result::factory()->create(['entry_id' => $entry->id, 'placement' => '3rd']);

    expect($result->points())->toBe(1);

    $this->actingAs($judge)
        ->patch(route('judge.results.update', [$section, $class, $result]), [
            'placement' => '1st',
        ]);

    expect($result->fresh()->points())->toBe(3);
});
