<?php

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\PrizeLevel;
use App\Models\Result;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists exhibitors ordered by sort name with entries, fee, prize money and net balance', function () {
    $prizeLevel = PrizeLevel::factory()->create(['first_place_pence' => 300]);
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->for($prizeLevel, 'prizeLevel')->create(['show_section_id' => $section->id]);

    $alice = Exhibitor::factory()->adult()->create(['first_name' => 'Alice', 'last_name' => 'Smith', 'full_name' => 'Alice Smith', 'sort_name' => 'Smith, Alice']);
    $entry = Entry::factory()->for($class, 'showClass')->for($alice)->create();
    Result::factory()->for($entry)->create(['placement' => '1st']);

    $bob = Exhibitor::factory()->junior()->create(['first_name' => 'Bob', 'last_name' => 'Jones', 'full_name' => 'Bob Jones', 'sort_name' => 'Jones, Bob']);
    Entry::factory()->for($class, 'showClass')->for($bob)->create();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.net-balances'));

    $response->assertOk()
        ->assertSeeInOrder(['Bob Jones', 'Alice Smith']);

    $feePence = $alice->feeOwedPence();
    $response->assertSee('£'.number_format($feePence / 100, 2))
        ->assertSee('£3.00')
        ->assertSee('£'.number_format((300 - $feePence) / 100, 2));
});

it('shows totals for entry fees, prize money and net balance', function () {
    $prizeLevel = PrizeLevel::factory()->create(['first_place_pence' => 300]);
    $section = ShowSection::factory()->create();
    $class = ShowClass::factory()->for($prizeLevel, 'prizeLevel')->create(['show_section_id' => $section->id]);

    $exhibitor = Exhibitor::factory()->adult()->create();
    $entry = Entry::factory()->for($class, 'showClass')->for($exhibitor)->create();
    Result::factory()->for($entry)->create(['placement' => '1st']);

    $response = $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.net-balances'));

    $feePence = $exhibitor->feeOwedPence();
    $netPence = 300 - $feePence;

    $response->assertOk()
        ->assertSee('Total Entry Fees')
        ->assertSee('Total Prize Money')
        ->assertSee('Total Net Balance')
        ->assertSee('£'.number_format($feePence / 100, 2))
        ->assertSee('£3.00')
        ->assertSee(($netPence < 0 ? '−' : '').'£'.number_format(abs($netPence) / 100, 2));
});

it('guest is redirected to login', function () {
    $this->get(route('admin.net-balances'))
        ->assertRedirect(route('login'));
});

it('judge receives 403', function () {
    $this->actingAs(User::factory()->judge()->create())
        ->get(route('admin.net-balances'))
        ->assertForbidden();
});
