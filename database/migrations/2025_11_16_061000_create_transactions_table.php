<?php

use App\Enums\TransactionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();
            $table->foreignId('account_id')
                ->constrained('user_accounts')
                ->cascadeOnDelete();
            $table->foreignId('destination_account_id')
                ->nullable()
                ->constrained('user_accounts')
                ->nullOnDelete();
            $table->timestamp('transaction_date')->useCurrent();
            $table->enum('type', TransactionType::values());
            $table->decimal('amount', 15, 2);
            $table->text('desc')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('transaction_date');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
