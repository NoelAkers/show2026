<?php

namespace App\Models;

use App\Enums\TransactionType;
use Database\Factories\ExhibitorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'first_name', 'last_name', 'full_name', 'sort_name', 'email',
    'type', 'is_resident', 'is_novice',
])]
class Exhibitor extends Model
{
    /** @use HasFactory<ExhibitorFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_resident' => 'boolean',
            'is_novice' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
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

    public function winningsPence(): int
    {
        return $this->entries()
            ->with(['result', 'showClass.prizeLevel'])
            ->get()
            ->sum(function (Entry $entry) {
                $result = $entry->result;
                $prizeLevel = $entry->showClass?->prizeLevel;

                if (! $result || ! $prizeLevel) {
                    return 0;
                }

                return match ($result->placement) {
                    '1st' => $prizeLevel->first_place_pence,
                    '2nd' => $prizeLevel->second_place_pence,
                    '3rd' => $prizeLevel->third_place_pence,
                    default => 0,
                };
            });
    }

    public function receivedFromExhibitorPence(): int
    {
        return $this->transactions()
            ->whereIn('type', [TransactionType::CashReceipt, TransactionType::CardPayment])
            ->sum('amount_pence');
    }

    public function paidToExhibitorPence(): int
    {
        return $this->transactions()
            ->where('type', TransactionType::CashPayment)
            ->sum('amount_pence');
    }

    public function amountPaidPence(): int
    {
        return $this->receivedFromExhibitorPence() - $this->paidToExhibitorPence();
    }

    public function balancePence(): int
    {
        return $this->feeOwedPence() - $this->winningsPence() - $this->amountPaidPence();
    }

    /**
     * Signed balance owed to the exhibitor: positive means the show owes
     * the exhibitor money, negative means the exhibitor owes the show.
     */
    public function balanceDueToExhibitorPence(): int
    {
        return -$this->balancePence();
    }

    public function hasPaid(): bool
    {
        return $this->balancePence() <= 0;
    }

    /**
     * Default amount for a "from exhibitor" transaction: the net balance
     * owed by the exhibitor to the show, i.e. the entry fee outstanding
     * after payments already received, offset by any winnings due.
     */
    public function outstandingFromExhibitorPence(): int
    {
        return max(0, $this->balancePence());
    }

    /**
     * Default amount for a "to exhibitor" transaction: winnings and any
     * overpayment not yet paid out.
     */
    public function owedToExhibitorPence(): int
    {
        return max(0, $this->balanceDueToExhibitorPence());
    }
}
