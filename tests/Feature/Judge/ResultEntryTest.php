<?php

use App\Livewire\Judge\Results\Section;
use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

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
    $class = ShowClass::factory()->create(['show_section_id' => $section->id, 'name' => 'Roses']);
    Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge)
        ->get(route('judge.sections.show', $section))
        ->assertOk()
        ->assertSee('Roses');
});

it('judge cannot view classes in an unassigned section', function () {
    [$judge] = judgeWithSection();
    $other = ShowSection::factory()->create();

    $this->actingAs($judge);

    Livewire::test(Section::class, ['showSection' => $other])
        ->assertForbidden();
});

it('judge can save placements and notes for entries in a class', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge);

    Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry->id}", '1st')
        ->set("notes.{$entry->id}", 'Beautiful arrangement.')
        ->call('saveClass', $class->id);

    expect(Result::where('entry_id', $entry->id)->first())
        ->placement->toBe('1st')
        ->notes->toBe('Beautiful arrangement.')
        ->entered_by_user_id->toBe($judge->id);
});

it('judge cannot assign a second 1st place in the same class', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge);

    $component = Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry1->id}", '1st')
        ->set("placements.{$entry2->id}", '1st')
        ->call('saveClass', $class->id);

    expect($component->get('classErrors'))->toHaveKey($class->id);
});

it('judge cannot assign a second 2nd place in the same class', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge);

    $component = Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry1->id}", '2nd')
        ->set("placements.{$entry2->id}", '2nd')
        ->call('saveClass', $class->id);

    expect($component->get('classErrors'))->toHaveKey($class->id);
});

it('judge cannot assign a second 3rd place in the same class', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge);

    $component = Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry1->id}", '3rd')
        ->set("placements.{$entry2->id}", '3rd')
        ->call('saveClass', $class->id);

    expect($component->get('classErrors'))->toHaveKey($class->id);
});

it('multiple highly commended in one class is allowed', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge);

    Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry1->id}", 'highly_commended')
        ->set("placements.{$entry2->id}", 'highly_commended')
        ->call('saveClass', $class->id)
        ->assertHasNoErrors();

    expect(Result::count())->toBe(2);
});

it('judge can clear a placement by saving with no placement selected', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry->id, 'placement' => '1st']);

    $this->actingAs($judge);

    Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry->id}", '')
        ->call('saveClass', $class->id)
        ->assertHasNoErrors();

    expect(Result::where('entry_id', $entry->id)->first()->placement)->toBeNull();
});

it('judge can change an existing placement', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry->id, 'placement' => '2nd']);

    $this->actingAs($judge);

    Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry->id}", '1st')
        ->call('saveClass', $class->id)
        ->assertHasNoErrors();

    expect(Result::where('entry_id', $entry->id)->first()->placement)->toBe('1st');
});

it('uniqueness rule is enforced when updating existing results', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry1->id, 'placement' => '1st']);
    Result::factory()->create(['entry_id' => $entry2->id, 'placement' => '2nd']);

    $this->actingAs($judge);

    $component = Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry2->id}", '1st')
        ->call('saveClass', $class->id);

    expect($component->get('classErrors'))->toHaveKey($class->id);
});

it('exhibitor points total changes after updating a result', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create();
    $entry = Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);
    $result = Result::factory()->create(['entry_id' => $entry->id, 'placement' => '3rd']);

    expect($result->points())->toBe(1);

    $this->actingAs($judge);

    Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry->id}", '1st')
        ->call('saveClass', $class->id);

    expect($result->fresh()->points())->toBe(3);
});

it('exhibitor name is not shown to the judge', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $exhibitor = Exhibitor::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);
    Entry::factory()->create(['show_class_id' => $class->id, 'exhibitor_id' => $exhibitor->id]);

    $this->actingAs($judge);

    Livewire::test(Section::class, ['showSection' => $section])
        ->assertDontSee('Jane Smith');
});

it('entries are ordered by entry number', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    Entry::factory()->create(['show_class_id' => $class->id]);
    Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge);

    $component = Livewire::test(Section::class, ['showSection' => $section]);

    $ids = array_keys($component->get('placements'));
    expect($ids[0])->toBeLessThan($ids[1]);
});

it('entered result is immediately visible to admin', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge);

    Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry->id}", '1st')
        ->call('saveClass', $class->id);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.show-sections.show-classes.show', [$section, $class]))
        ->assertOk()
        ->assertSee('1st');
});

it('unauthorized judge cannot access results for another section', function () {
    [$judge] = judgeWithSection();
    $otherSection = ShowSection::factory()->create();

    $this->actingAs($judge);

    Livewire::test(Section::class, ['showSection' => $otherSection])
        ->assertForbidden();
});

it('saving one class does not affect results for entries in another class', function () {
    [$judge, $section] = judgeWithSection();
    $class1 = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $class2 = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class1->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class2->id]);

    $this->actingAs($judge);

    Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry1->id}", '1st')
        ->set("placements.{$entry2->id}", '2nd')
        ->call('saveClass', $class1->id);

    expect(Result::where('entry_id', $entry1->id)->exists())->toBeTrue()
        ->and(Result::where('entry_id', $entry2->id)->exists())->toBeFalse();
});

it('discarding a class resets placements and notes to last saved values', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry = Entry::factory()->create(['show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $entry->id, 'placement' => '2nd', 'notes' => 'Original note']);

    $this->actingAs($judge);

    $component = Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry->id}", '1st')
        ->set("notes.{$entry->id}", 'Changed note')
        ->call('discardClass', $class->id);

    expect($component->get("placements.{$entry->id}"))->toBe('2nd')
        ->and($component->get("notes.{$entry->id}"))->toBe('Original note');
});

it('discarding a class clears any class errors', function () {
    [$judge, $section] = judgeWithSection();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class->id]);

    $this->actingAs($judge);

    $component = Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry1->id}", '1st')
        ->set("placements.{$entry2->id}", '1st')
        ->call('saveClass', $class->id)
        ->call('discardClass', $class->id);

    expect($component->get('classErrors'))->not->toHaveKey($class->id);
});

it('discarding a class does not affect other entries', function () {
    [$judge, $section] = judgeWithSection();
    $class1 = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $class2 = ShowClass::factory()->create(['show_section_id' => $section->id]);
    $entry1 = Entry::factory()->create(['show_class_id' => $class1->id]);
    $entry2 = Entry::factory()->create(['show_class_id' => $class2->id]);

    $this->actingAs($judge);

    $component = Livewire::test(Section::class, ['showSection' => $section])
        ->set("placements.{$entry1->id}", '1st')
        ->set("placements.{$entry2->id}", '2nd')
        ->call('discardClass', $class1->id);

    expect($component->get("placements.{$entry2->id}"))->toBe('2nd');
});
