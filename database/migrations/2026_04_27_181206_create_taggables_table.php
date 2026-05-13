<?php

declare(strict_types=1);

use App\Models\RecurringTransaction;
use App\Models\Tag;
use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('taggables', function (Blueprint $table): void {
            $table
                ->foreignIdFor(Tag::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->string('taggable_id');
            $table->string('taggable_type');

            $table->primary(['tag_id', 'taggable_id', 'taggable_type'], 'taggables_pkey');
            $table->index(['taggable_id', 'taggable_type']);
        });

        DB::table('transactions')
            ->whereNotNull('tag_id')
            ->orderBy('id')
            ->select(['id', 'tag_id'])
            ->chunk(500, function ($rows): void {
                DB::table('taggables')->insert(
                    $rows->map(fn ($row): array => [
                        'tag_id'        => $row->tag_id,
                        'taggable_id'   => (string) $row->id,
                        'taggable_type' => Transaction::class,
                    ])->all()
                );
            });

        DB::table('recurring_transactions')
            ->whereNotNull('tag_id')
            ->orderBy('id')
            ->select(['id', 'tag_id'])
            ->chunk(500, function ($rows): void {
                DB::table('taggables')->insert(
                    $rows->map(fn ($row): array => [
                        'tag_id'        => $row->tag_id,
                        'taggable_id'   => (string) $row->id,
                        'taggable_type' => RecurringTransaction::class,
                    ])->all()
                );
            });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tag_id');
        });

        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tag_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table
                ->foreignIdFor(Tag::class)
                ->nullable()
                ->after('category_id')
                ->constrained()
                ->cascadeOnDelete();
        });

        Schema::table('recurring_transactions', function (Blueprint $table): void {
            $table
                ->foreignIdFor(Tag::class)
                ->nullable()
                ->after('category_id')
                ->constrained()
                ->cascadeOnDelete();
        });

        DB::table('taggables')
            ->where('taggable_type', Transaction::class)
            ->orderBy('taggable_id')
            ->select(['tag_id', 'taggable_id'])
            ->chunk(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('transactions')
                        ->where('id', $row->taggable_id)
                        ->update(['tag_id' => $row->tag_id]);
                }
            });

        DB::table('taggables')
            ->where('taggable_type', RecurringTransaction::class)
            ->orderBy('taggable_id')
            ->select(['tag_id', 'taggable_id'])
            ->chunk(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('recurring_transactions')
                        ->where('id', $row->taggable_id)
                        ->update(['tag_id' => $row->tag_id]);
                }
            });

        Schema::dropIfExists('taggables');
    }
};
