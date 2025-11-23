<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\UserAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (self $user): void {
            if ($user->is_admin) {
                return;
            }

            $primaryAccount = Account::query()
                ->where('is_primary', true)
                ->whereNull('user_id')
                ->orderBy('id')
                ->first();

            if (! $primaryAccount) {
                return;
            }

            UserAccount::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'account_id' => $primaryAccount->id,
                ],
                [
                    'initial_balance' => 0,
                    'balance' => 0,
                    'is_primary' => true,
                ],
            );
        });
    }

    public function userAccounts(): HasMany
    {
        return $this->hasMany(UserAccount::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
