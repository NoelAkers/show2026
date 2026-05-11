<?php

namespace App\Models;

use Database\Factories\ShowClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['show_section_id', 'name', 'description', 'max_entries_per_exhibitor', 'sort_order', 'prize_level_id'])]
class ShowClass extends Model
{
    /** @use HasFactory<ShowClassFactory> */
    use HasFactory;

    public function showSection(): BelongsTo
    {
        return $this->belongsTo(ShowSection::class);
    }

    public function prizeLevel(): BelongsTo
    {
        return $this->belongsTo(PrizeLevel::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function trophies(): BelongsToMany
    {
        return $this->belongsToMany(Trophy::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
