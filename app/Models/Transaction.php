<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\UserAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Snapshot of original attributes used during balance recalculations.
     *
     * @var array<string, mixed>
     */
    protected array $balanceOriginal = [];

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

            $typeValue = static::resolveTypeValue($transaction->type);

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

        static::creating(function (Transaction $transaction): void {
            static::ensureSufficientFunds($transaction);
        });

        static::created(function (Transaction $transaction): void {
            static::syncAccountBalances($transaction->user_id);
        });

        static::updating(function (Transaction $transaction): void {
            $transaction->balanceOriginal = $transaction->getOriginal();
            static::ensureSufficientFunds($transaction, $transaction->balanceOriginal);
        });

        static::updated(function (Transaction $transaction): void {
            if (! empty($transaction->balanceOriginal)) {
                static::applyBalanceEffect($transaction->balanceOriginal, reverse: true);
            }

            static::applyBalanceEffect($transaction);
            $transaction->balanceOriginal = [];
        });

        static::deleting(function (Transaction $transaction): void {
            static::applyBalanceEffect($transaction, reverse: true);
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

    protected static function ensureSufficientFunds(Transaction $transaction, ?array $original = null): void
    {
        $typeValue = static::resolveTypeValue($transaction->type);

        if (! in_array($typeValue, [TransactionType::Expense->value, TransactionType::Transfer->value], true)) {
            return;
        }

        $sourceAccount = static::findAccount($transaction->account_id);

        if (! $sourceAccount) {
            static::throwValidation('account_id', 'The selected account is unavailable.');
        }

        $availableBalance = (float) $sourceAccount->balance;

        if ($original) {
            $originalType = static::resolveTypeValue($original['type'] ?? null);
            $originalAccountId = $original['account_id'] ?? null;

            if (
                in_array($originalType, [TransactionType::Expense->value, TransactionType::Transfer->value], true)
                && $originalAccountId === $transaction->account_id
            ) {
                $availableBalance += (float) ($original['amount'] ?? 0);
            }
        }

        if ($availableBalance < (float) $transaction->amount) {
            static::throwValidation('amount', 'Insufficient balance for this transaction.');
        }

        if ($typeValue === TransactionType::Transfer->value && $transaction->destination_account_id === $transaction->account_id) {
            static::throwValidation('destination_account_id', 'Destination account must be different from the source.');
        }

        if ($typeValue === TransactionType::Transfer->value) {
            if (! static::findAccount($transaction->destination_account_id)) {
                static::throwValidation('destination_account_id', 'Destination account is unavailable.');
            }
        }
    }

    protected static function applyBalanceEffect(Transaction|array $data, bool $reverse = false): void
    {
        $typeValue = static::resolveTypeValue($data instanceof Transaction ? $data->type : ($data['type'] ?? null));
        $amount = (float) ($data instanceof Transaction ? $data->amount : ($data['amount'] ?? 0));
        $accountId = $data instanceof Transaction ? $data->account_id : ($data['account_id'] ?? null);
        $destinationId = $data instanceof Transaction ? $data->destination_account_id : ($data['destination_account_id'] ?? null);
        $multiplier = $reverse ? -1 : 1;

        if (! $typeValue || $amount === 0.0) {
            return;
        }

        $delta = $amount * $multiplier;

        if ($typeValue === TransactionType::Income->value) {
            static::adjustAccountBalance($accountId, $delta);

            return;
        }

        if ($typeValue === TransactionType::Expense->value) {
            static::adjustAccountBalance($accountId, -$delta);

            return;
        }

        if ($typeValue === TransactionType::Transfer->value) {
            static::adjustAccountBalance($accountId, -$delta);
            static::adjustAccountBalance($destinationId, $delta);
        }
    }

    protected static function adjustAccountBalance(?int $userAccountId, float $delta): void
    {
        if (! $userAccountId || $delta === 0.0) {
            return;
        }

        $account = static::findAccount($userAccountId);

        if (! $account) {
            return;
        }

        $account->balance = round((float) $account->balance + $delta, 2);
        $account->save();
    }

    protected static function findAccount(?int $id): ?UserAccount
    {
        if (! $id) {
            return null;
        }

        return UserAccount::query()->find($id);
    }

    protected static function resolveTypeValue(mixed $type): ?string
    {
        if ($type instanceof TransactionType) {
            return $type->value;
        }

        return $type ? (string) $type : null;
    }

    /**
     * Recalculate and sync balances for user accounts from all transactions.
     */
    public static function syncAccountBalances(?int $userId = null): int
    {
        $accounts = UserAccount::query()
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->get();

        $accountIds = $accounts->pluck('id')->all();

        if ($accountIds === []) {
            return 0;
        }

        $transactionSums = static::query()
            ->selectRaw('account_id, destination_account_id, type, SUM(amount) as total')
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->groupBy('account_id', 'destination_account_id', 'type')
            ->get();

        $byAccount = [];

        foreach ($transactionSums as $row) {
            $typeValue = static::resolveTypeValue($row->type);
            $accountId = $row->account_id;
            $destinationId = $row->destination_account_id;
            $total = (float) $row->total;

            if ($typeValue === TransactionType::Income->value && $accountId) {
                $byAccount[$accountId]['income'] = ($byAccount[$accountId]['income'] ?? 0.0) + $total;
            }

            if ($typeValue === TransactionType::Expense->value && $accountId) {
                $byAccount[$accountId]['expense'] = ($byAccount[$accountId]['expense'] ?? 0.0) + $total;
            }

            if ($typeValue === TransactionType::Transfer->value && $accountId) {
                $byAccount[$accountId]['transfer_out'] = ($byAccount[$accountId]['transfer_out'] ?? 0.0) + $total;
            }

            if ($typeValue === TransactionType::Transfer->value && $destinationId) {
                $byAccount[$destinationId]['transfer_in'] = ($byAccount[$destinationId]['transfer_in'] ?? 0.0) + $total;
            }
        }

        $updated = 0;

        DB::transaction(function () use ($accounts, $byAccount, &$updated): void {
            foreach ($accounts as $account) {
                $income = $byAccount[$account->id]['income'] ?? 0.0;
                $expense = $byAccount[$account->id]['expense'] ?? 0.0;
                $transferIn = $byAccount[$account->id]['transfer_in'] ?? 0.0;
                $transferOut = $byAccount[$account->id]['transfer_out'] ?? 0.0;

                $account->balance = round(
                    (float) $account->initial_balance
                    + $income
                    + $transferIn
                    - $expense
                    - $transferOut,
                    2
                );

                $account->save();
                $updated++;
            }
        });

        return $updated;
    }

    protected static function throwValidation(string $field, string $message): never
    {
        throw ValidationException::withMessages(static::validationMessages($field, $message));
    }

    /**
     * @return array<string, array{0: string}>
     */
    protected static function validationMessages(string $field, string $message): array
    {
        $messages = [];

        foreach (static::fieldKeys($field) as $key) {
            $messages[$key] = [$message];
        }

        return $messages;
    }

    /**
     * @return list<string>
     */
    protected static function fieldKeys(string $field): array
    {
        $keys = [];

        if ($mountedKey = static::mountedActionFieldKey($field)) {
            $keys[] = $mountedKey;
        }

        $keys[] = "data.{$field}";
        $keys[] = $field;

        return array_values(array_unique(array_filter($keys)));
    }

    protected static function mountedActionFieldKey(string $field): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        $request = request();

        if (! $request->headers->has('X-Livewire')) {
            return null;
        }

        $mountedActions = data_get($request->input('serverMemo'), 'data.mountedActions');

        if (is_array($mountedActions) && $mountedActions !== []) {
            $index = array_key_last($mountedActions);

            return "mountedActions.{$index}.data.{$field}";
        }

        return "mountedActions.0.data.{$field}";
    }
}
