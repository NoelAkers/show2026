<?php

namespace App\Models;

use Database\Factories\ShowSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'sort_order'])]
class ShowSection extends Model
{
    /** @use HasFactory<ShowSectionFactory> */
    use HasFactory;

    public function showClasses(): HasMany
    {
        return $this->hasMany(ShowClass::class);
    }

    public function judges(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
