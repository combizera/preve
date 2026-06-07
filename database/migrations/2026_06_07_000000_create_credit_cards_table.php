<?php

declare(strict_types=1);

use App\Models\CreditCard;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('credit_cards', function (Blueprint $table): void {
            $table->id();
            $table
                ->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('last_four', 4)->nullable();
            $table->smallInteger('closing_day');
            $table->smallInteger('due_day');
            $table->string('color');
            $table->integer('credit_limit')->nullable();
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table
                ->foreignIdFor(CreditCard::class)
                ->nullable()
                ->after('savings_bucket_id')
                ->constrained()
                ->nullOnDelete();
            $table->date('purchase_date')->nullable()->after('transaction_date');
            $table->smallInteger('split_number')->nullable()->after('purchase_date');
            $table->smallInteger('split_total')->nullable()->after('split_number');
            $table
                ->foreignIdFor(Transaction::class, 'parent_transaction_id')
                ->nullable()
                ->after('split_total')
                ->constrained('transactions')
                ->nullOnDelete();
        });

        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table
                ->foreignIdFor(CreditCard::class)
                ->nullable()
                ->after('category_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignIdFor(CreditCard::class);
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parent_transaction_id');
            $table->dropColumn(['purchase_date', 'split_number', 'split_total']);
            $table->dropConstrainedForeignIdFor(CreditCard::class);
        });

        Schema::dropIfExists('credit_cards');
    }
};
