<?php

use App\Models\Entry;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function judgeWithToken(): array
{
    $user = User::factory()->judge()->create();

    return [$user, $user->createToken('test')->plainTextToken];
}

function classWithEntries(int $count = 3): array
{
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->for($section)->create();
    $entries = Entry::factory()->for($class, 'showClass')->count($count)->create();

    return [$class, $entries];
}

test('judge can submit results for a class', function () {
    [$user, $token] = judgeWithToken();
    [$class, $entries] = classWithEntries(3);

    $this->withToken($token)->postJson('/api/results', [
        'show_class_id' => $class->id,
        'results' => [
            ['entry_number' => $entries[0]->entry_number, 'placement' => 'first'],
            ['entry_number' => $entries[1]->entry_number, 'placement' => 'second'],
            ['entry_number' => $entries[2]->entry_number, 'placement' => 'highlyCommended'],
        ],
    ])->assertCreated();

    expect(Result::count())->toBe(3);
});

test('placements are translated to db format', function () {
    [$user, $token] = judgeWithToken();
    [$class, $entries] = classWithEntries(2);

    $this->withToken($token)->postJson('/api/results', [
        'show_class_id' => $class->id,
        'results' => [
            ['entry_number' => $entries[0]->entry_number, 'placement' => 'first'],
            ['entry_number' => $entries[1]->entry_number, 'placement' => 'highlyCommended'],
        ],
    ])->assertCreated();

    expect(Result::where('placement', '1st')->exists())->toBeTrue()
        ->and(Result::where('placement', 'highly_commended')->exists())->toBeTrue();
});

test('duplicate unique placement within submission is rejected', function () {
    [$user, $token] = judgeWithToken();
    [$class, $entries] = classWithEntries(2);

    $this->withToken($token)->postJson('/api/results', [
        'show_class_id' => $class->id,
        'results' => [
            ['entry_number' => $entries[0]->entry_number, 'placement' => 'first'],
            ['entry_number' => $entries[1]->entry_number, 'placement' => 'first'],
        ],
    ])->assertUnprocessable()->assertJsonStructure(['message']);
});

test('placement already in db for class is rejected', function () {
    [$user, $token] = judgeWithToken();
    [$class, $entries] = classWithEntries(2);

    // Pre-existing first place result in the class.
    Result::create(['entry_id' => $entries[0]->id, 'placement' => '1st', 'entered_by_user_id' => $user->id]);

    $this->withToken($token)->postJson('/api/results', [
        'show_class_id' => $class->id,
        'results' => [
            ['entry_number' => $entries[1]->entry_number, 'placement' => 'first'],
        ],
    ])->assertUnprocessable()
        ->assertJson(['message' => 'First place has already been awarded in this class.']);
});

test('entry from wrong class is rejected', function () {
    [$user, $token] = judgeWithToken();
    $section = ShowSection::factory()->create();
    $classA = ShowClass::factory()->for($section)->create();
    $classB = ShowClass::factory()->for($section)->create();
    $entry = Entry::factory()->for($classA, 'showClass')->create();

    $this->withToken($token)->postJson('/api/results', [
        'show_class_id' => $classB->id,
        'results' => [
            ['entry_number' => $entry->entry_number, 'placement' => 'first'],
        ],
    ])->assertUnprocessable()->assertJsonStructure(['message']);
});

test('unknown entry number is rejected', function () {
    [$user, $token] = judgeWithToken();
    [$class] = classWithEntries(0);

    $this->withToken($token)->postJson('/api/results', [
        'show_class_id' => $class->id,
        'results' => [
            ['entry_number' => 9999, 'placement' => 'first'],
        ],
    ])->assertUnprocessable()->assertJsonStructure(['message']);
});

test('multiple highly commended entries are accepted', function () {
    [$user, $token] = judgeWithToken();
    [$class, $entries] = classWithEntries(3);

    $this->withToken($token)->postJson('/api/results', [
        'show_class_id' => $class->id,
        'results' => [
            ['entry_number' => $entries[0]->entry_number, 'placement' => 'highlyCommended'],
            ['entry_number' => $entries[1]->entry_number, 'placement' => 'highlyCommended'],
            ['entry_number' => $entries[2]->entry_number, 'placement' => 'highlyCommended'],
        ],
    ])->assertCreated();
});

test('invalid placement value is rejected', function () {
    [$user, $token] = judgeWithToken();
    [$class, $entries] = classWithEntries(1);

    $this->withToken($token)->postJson('/api/results', [
        'show_class_id' => $class->id,
        'results' => [
            ['entry_number' => $entries[0]->entry_number, 'placement' => '1st'],
        ],
    ])->assertUnprocessable();
});
