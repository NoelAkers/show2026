<?php

use App\Models\Result;

it('prizeLabel returns correct labels', function ($placement, $expected) {
    $result = new Result(['placement' => $placement]);

    expect($result->prizeLabel())->toBe($expected);
})->with([
    ['1st', '1st Prize'],
    ['2nd', '2nd Prize'],
    ['3rd', '3rd Prize'],
    ['highly_commended', 'Highly Commended'],
]);
