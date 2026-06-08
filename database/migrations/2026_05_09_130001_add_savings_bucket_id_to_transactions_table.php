<?php

declare(strict_types=1);

use App\Models\SavingsBucket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table
                ->foreignIdFor(SavingsBucket::class)
                ->nullable()
                ->after('category_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignIdFor(SavingsBucket::class);
        });
    }
};
