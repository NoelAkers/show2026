<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('returns entry with belongs_to_class true when entry is in the class', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->for($section)->create();
    $exhibitor = Exhibitor::factory()->create(['full_name' => 'Alice Brown']);
    $entry = Entry::factory()->for($class, 'showClass')->for($exhibitor)->create();

    $token = User::factory()->judge()->create()->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson("/api/entries/{$entry->entry_number}?show_class_id={$class->id}")
        ->assertOk()
        ->assertJson(['data' => [
            'entry_number' => $entry->entry_number,
            'exhibitor_name' => 'Alice Brown',
            'show_class_id' => $class->id,
            'belongs_to_class' => true,
        ]]);
});

test('returns belongs_to_class false when entry belongs to a different class', function () {
    $section = ShowSection::factory()->create();
    $classA = ShowClass::factory()->for($section)->create();
    $classB = ShowClass::factory()->for($section)->create();
    $entry = Entry::factory()->for($classA, 'showClass')->create();

    $token = User::factory()->judge()->create()->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson("/api/entries/{$entry->entry_number}?show_class_id={$classB->id}")
        ->assertOk()
        ->assertJson(['data' => ['belongs_to_class' => false]]);
});

test('returns 404 when entry number does not exist', function () {
    $token = User::factory()->judge()->create()->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/entries/9999')
        ->assertNotFound();
});
