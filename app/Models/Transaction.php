<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'account_id',
        'destination_account_id',
        'type',
        'amount',
        'desc',
        'transaction_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
            'transaction_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Transaction $transaction): void {
            if (! $transaction->user_id) {
                $transaction->user_id = Auth::id();
            }

            $type = $transaction->type;
            $typeValue = $type instanceof TransactionType ? $type->value : $type;

            if ($typeValue === TransactionType::Income->value) {
                $transaction->account_id = $transaction->account_id ?? $transaction->destination_account_id;
                $transaction->destination_account_id = null;
            }

            if ($typeValue === TransactionType::Expense->value) {
                $transaction->destination_account_id = null;
            }

            if ($typeValue !== TransactionType::Transfer->value) {
                $transaction->destination_account_id = $transaction->destination_account_id ?: null;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'account_id');
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'destination_account_id');
    }
}
