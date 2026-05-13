<?php

declare(strict_types=1);

use App\Models\RecurringTransaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::table('recurring_transactions')
            ->whereNull('uuid')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('recurring_transactions')
                        ->where('id', $row->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                }
            });

        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable(false)->change();
        });

        DB::statement(
            'UPDATE taggables SET taggable_id = r.uuid::text
             FROM recurring_transactions r
             WHERE taggables.taggable_type = ?
             AND taggables.taggable_id = r.id::text',
            [RecurringTransaction::class],
        );

        Schema::table('transactions', function (Blueprint $table): void {
            $table->uuid('recurring_transaction_uuid')->nullable()->after('recurring_transaction_id');
        });

        DB::statement(
            'UPDATE transactions
             SET recurring_transaction_uuid = r.uuid
             FROM recurring_transactions r
             WHERE transactions.recurring_transaction_id = r.id',
        );

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('recurring_transaction_id');
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->renameColumn('recurring_transaction_uuid', 'recurring_transaction_id');
        });

        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table->dropPrimary();
        });

        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table->dropColumn('id');
        });

        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table->renameColumn('uuid', 'id');
        });

        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table->primary('id');
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->foreign('recurring_transaction_id')
                ->references('id')
                ->on('recurring_transactions')
                ->nullOnDelete();
        });
    }
};
