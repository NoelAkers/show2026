<?php

namespace App\Models;

use Database\Factories\ExhibitorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'first_name', 'last_name', 'full_name', 'sort_name',
    'type', 'is_resident', 'has_paid', 'is_novice', 'amount_paid_pence',
])]
class Exhibitor extends Model
{
    /** @use HasFactory<ExhibitorFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_resident' => 'boolean',
            'has_paid' => 'boolean',
            'is_novice' => 'boolean',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function isAdult(): bool
    {
        return $this->type === 'adult';
    }

    public function isJunior(): bool
    {
        return $this->type === 'junior';
    }

    public function totalEntries(): int
    {
        return $this->entries()->count();
    }

    public function chargeableEntries(): int
    {
        if ($this->isJunior()) {
            return 0;
        }

        return min($this->totalEntries(), 10);
    }

    public function feeOwedPence(): int
    {
        return $this->chargeableEntries() * config('show.entry_fee_pence');
    }

    public function balancePence(): int
    {
        return $this->feeOwedPence() - $this->amount_paid_pence;
    }
}
