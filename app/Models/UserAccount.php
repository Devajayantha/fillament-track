<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class UserAccount extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'account_id',
        'user_id',
        'initial_balance',
        'balance',
        'is_primary',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'balance' => 'decimal:2',
            'is_primary' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (UserAccount $userAccount): void {
            if ($userAccount->balance === null) {
                $userAccount->balance = $userAccount->initial_balance;
            }
        });

        static::updating(function (UserAccount $userAccount): void {
            if ($userAccount->isDirty('initial_balance') && $userAccount->hasTransactions()) {
                throw ValidationException::withMessages([
                    'initial_balance' => 'Initial balance cannot be changed after transactions exist.',
                ]);
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    public function destinationTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'destination_account_id');
    }

    public function hasTransactions(): bool
    {
        return $this->sourceTransactions()->exists() || $this->destinationTransactions()->exists();
    }
}
