<?php

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
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
            $table->string('type')->default('EXPENSE');
            $table->text('description');
            $table->text('notes')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
