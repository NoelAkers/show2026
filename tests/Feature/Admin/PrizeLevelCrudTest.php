<?php

use App\Models\PrizeLevel;
use App\Models\ShowClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function prizeLevelAdmin(): User
{
    return User::factory()->admin()->create();
}

it('admin can view prize levels list', function () {
    $prizeLevel = PrizeLevel::factory()->create(['name' => 'Standard']);

    $this->actingAs(prizeLevelAdmin())
        ->get(route('admin.prize-levels.index'))
        ->assertOk()
        ->assertSee('Standard');
});

it('admin can create a prize level with valid data', function () {
    $this->actingAs(prizeLevelAdmin())
        ->post(route('admin.prize-levels.store'), [
            'name' => 'Top',
            'first_place_pence' => 500,
            'second_place_pence' => 250,
            'third_place_pence' => 100,
        ])
        ->assertRedirect(route('admin.prize-levels.index'));

    $this->assertDatabaseHas('prize_levels', [
        'name' => 'Top',
        'first_place_pence' => 500,
        'second_place_pence' => 250,
        'third_place_pence' => 100,
    ]);
});

it('creating a prize level with missing fields fails validation', function () {
    $this->actingAs(prizeLevelAdmin())
        ->post(route('admin.prize-levels.store'), [
            'name' => '',
        ])
        ->assertSessionHasErrors(['name', 'first_place_pence', 'second_place_pence', 'third_place_pence']);
});

it('admin can update an existing prize level', function () {
    $prizeLevel = PrizeLevel::factory()->create(['name' => 'Old Name']);

    $this->actingAs(prizeLevelAdmin())
        ->put(route('admin.prize-levels.update', $prizeLevel), [
            'name' => 'New Name',
            'first_place_pence' => 400,
            'second_place_pence' => 200,
            'third_place_pence' => 100,
        ])
        ->assertRedirect(route('admin.prize-levels.index'));

    $fresh = $prizeLevel->fresh();
    expect($fresh->name)->toBe('New Name')
        ->and($fresh->first_place_pence)->toBe(400);
});

it('admin can delete a prize level with no classes', function () {
    $prizeLevel = PrizeLevel::factory()->create();

    $this->actingAs(prizeLevelAdmin())
        ->delete(route('admin.prize-levels.destroy', $prizeLevel))
        ->assertRedirect(route('admin.prize-levels.index'));

    $this->assertDatabaseMissing('prize_levels', ['id' => $prizeLevel->id]);
});

it('admin cannot delete a prize level assigned to a class', function () {
    $prizeLevel = PrizeLevel::factory()->create();
    ShowClass::factory()->for($prizeLevel, 'prizeLevel')->create();

    $this->actingAs(prizeLevelAdmin())
        ->delete(route('admin.prize-levels.destroy', $prizeLevel))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('prize_levels', ['id' => $prizeLevel->id]);
});

it('guest is redirected to login on prize levels index', function () {
    $this->get(route('admin.prize-levels.index'))->assertRedirect(route('login'));
});

it('non-admin receives 403 on prize levels index', function () {
    $this->actingAs(User::factory()->judge()->create())
        ->get(route('admin.prize-levels.index'))
        ->assertForbidden();
});
