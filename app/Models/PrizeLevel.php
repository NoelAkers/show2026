<?php

namespace App\Models;

use Database\Factories\PrizeLevelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'first_place_pence', 'second_place_pence', 'third_place_pence'])]
class PrizeLevel extends Model
{
    /** @use HasFactory<PrizeLevelFactory> */
    use HasFactory;

    public function showClasses(): HasMany
    {
        return $this->hasMany(ShowClass::class);
    }
}
