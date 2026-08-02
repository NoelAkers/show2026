<?php

use App\Livewire\Admin\Exhibitors\EntriesTable;
use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->exhibitor = Exhibitor::factory()->create();
});

it('sorts entries by entry number ascending by default', function () {
    $sectionA = ShowSection::factory()->create(['sort_order' => 1]);
    $sectionB = ShowSection::factory()->create(['sort_order' => 2]);
    $classA = ShowClass::factory()->create(['show_section_id' => $sectionA->id, 'sort_order' => 1]);
    $classB = ShowClass::factory()->create(['show_section_id' => $sectionB->id, 'sort_order' => 1]);

    $inClassB = Entry::factory()->create(['exhibitor_id' => $this->exhibitor->id, 'show_class_id' => $classB->id]);
    $inClassA = Entry::factory()->create(['exhibitor_id' => $this->exhibitor->id, 'show_class_id' => $classA->id]);

    Livewire::actingAs($this->admin)
        ->test(EntriesTable::class, ['exhibitor' => $this->exhibitor])
        ->assertSet('sortBy', 'entry_number')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder([$inClassB->formatted_entry_number, $inClassA->formatted_entry_number]);
});

it('toggles sort direction when the same column is clicked again', function () {
    Livewire::actingAs($this->admin)
        ->test(EntriesTable::class, ['exhibitor' => $this->exhibitor])
        ->assertSet('sortDirection', 'asc')
        ->call('sort', 'entry_number')
        ->assertSet('sortDirection', 'desc')
        ->call('sort', 'entry_number')
        ->assertSet('sortDirection', 'asc');
});

it('resets sort direction to ascending when a different column is clicked', function () {
    Livewire::actingAs($this->admin)
        ->test(EntriesTable::class, ['exhibitor' => $this->exhibitor])
        ->call('sort', 'entry_number')
        ->assertSet('sortDirection', 'desc')
        ->call('sort', 'placement')
        ->assertSet('sortBy', 'placement')
        ->assertSet('sortDirection', 'asc');
});

it('sorts by section and by class in the same composite order, honouring per-section class order', function () {
    $sectionA = ShowSection::factory()->create(['sort_order' => 1]);
    $sectionB = ShowSection::factory()->create(['sort_order' => 2]);
    $classA1 = ShowClass::factory()->create(['show_section_id' => $sectionA->id, 'sort_order' => 1]);
    $classA2 = ShowClass::factory()->create(['show_section_id' => $sectionA->id, 'sort_order' => 2]);
    $classB1 = ShowClass::factory()->create(['show_section_id' => $sectionB->id, 'sort_order' => 1]);

    $inClassB1 = Entry::factory()->create(['exhibitor_id' => $this->exhibitor->id, 'show_class_id' => $classB1->id]);
    $inClassA2 = Entry::factory()->create(['exhibitor_id' => $this->exhibitor->id, 'show_class_id' => $classA2->id]);
    $inClassA1 = Entry::factory()->create(['exhibitor_id' => $this->exhibitor->id, 'show_class_id' => $classA1->id]);

    $expectedOrder = [
        $inClassA1->formatted_entry_number,
        $inClassA2->formatted_entry_number,
        $inClassB1->formatted_entry_number,
    ];

    Livewire::actingAs($this->admin)
        ->test(EntriesTable::class, ['exhibitor' => $this->exhibitor])
        ->call('sort', 'section')
        ->assertSeeInOrder($expectedOrder);

    Livewire::actingAs($this->admin)
        ->test(EntriesTable::class, ['exhibitor' => $this->exhibitor])
        ->call('sort', 'class')
        ->assertSeeInOrder($expectedOrder);
});

it('sorts by placement rank, putting entries without a result last', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->create(['show_section_id' => $section->id]);

    $noResult = Entry::factory()->create(['exhibitor_id' => $this->exhibitor->id, 'show_class_id' => $class->id]);

    $thirdPlace = Entry::factory()->create(['exhibitor_id' => $this->exhibitor->id, 'show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $thirdPlace->id, 'placement' => '3rd']);

    $firstPlace = Entry::factory()->create(['exhibitor_id' => $this->exhibitor->id, 'show_class_id' => $class->id]);
    Result::factory()->create(['entry_id' => $firstPlace->id, 'placement' => '1st']);

    Livewire::actingAs($this->admin)
        ->test(EntriesTable::class, ['exhibitor' => $this->exhibitor])
        ->call('sort', 'placement')
        ->assertSeeInOrder([
            $firstPlace->formatted_entry_number,
            $thirdPlace->formatted_entry_number,
            $noResult->formatted_entry_number,
        ]);
});
