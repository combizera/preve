<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('hiatuses', function (Blueprint $table): void {
            $table->id();
            $table
                ->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->string('note')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hiatuses');
    }
};
