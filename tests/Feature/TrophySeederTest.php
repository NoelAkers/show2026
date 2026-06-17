<?php

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('UserSeeder creates Judge TBC with the email TrophySeeder queries by', function () {
    $this->seed(UserSeeder::class);

    expect(User::where('email', 'judge@villageshow.org')->where('name', 'Judge TBC')->exists())->toBeTrue();
});
