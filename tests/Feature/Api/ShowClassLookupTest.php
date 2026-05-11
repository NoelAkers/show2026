<?php

use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function judgeToken(): string
{
    return User::factory()->create(['is_judge' => true])->createToken('test')->plainTextToken;
}

test('returns show class by id', function () {
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->for($section)->create(['name' => 'Best Rose', 'description' => 'Single stem']);

    $this->withToken(judgeToken())
        ->getJson("/api/show-classes?number={$class->id}")
        ->assertOk()
        ->assertJson(['data' => [
            'id' => $class->id,
            'number' => $class->id,
            'name' => 'Best Rose',
            'description' => 'Single stem',
        ]]);
});

test('returns 404 when class does not exist', function () {
    $this->withToken(judgeToken())
        ->getJson('/api/show-classes?number=9999')
        ->assertNotFound();
});
