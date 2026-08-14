<?php

use App\Models\Result;

it('prizeLabel returns correct labels', function ($placement, $expected) {
    $result = new Result(['placement' => $placement]);

    expect($result->prizeLabel())->toBe($expected);
})->with([
    ['1st', '1st'],
    ['2nd', '2nd'],
    ['3rd', '3rd'],
    ['highly_commended', 'Highly Commended'],
]);
