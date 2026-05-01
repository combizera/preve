<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_series', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table
                ->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignIdFor(Category::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('default_amount');
            $table->text('default_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'category_id']);
        });

        Schema::create('forecasts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('forecast_series_id')
                ->constrained('forecast_series')
                ->cascadeOnDelete();
            $table
                ->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignIdFor(Category::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('amount');
            $table->date('month');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['forecast_series_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecasts');
        Schema::dropIfExists('forecast_series');
    }
};
