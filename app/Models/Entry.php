<?php

namespace App\Models;

use Database\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['show_class_id', 'exhibitor_id'])]
class Entry extends Model
{
    /** @use HasFactory<EntryFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Entry $entry) {
            $entry->entry_number = (self::max('entry_number') ?? 0) + 1;
        });
    }

    public function showClass(): BelongsTo
    {
        return $this->belongsTo(ShowClass::class);
    }

    public function exhibitor(): BelongsTo
    {
        return $this->belongsTo(Exhibitor::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }

    public function hasResult(): bool
    {
        return $this->result()->exists();
    }
}
