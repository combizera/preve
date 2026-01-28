<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignIdFor(Category::class, 'category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table
                ->foreignIdFor(Tag::class)
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('amount');
            $table->string('frequency');
            $table->string('type')->default('expense');
            $table->text('description');
            $table->smallInteger('day_of_month');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
