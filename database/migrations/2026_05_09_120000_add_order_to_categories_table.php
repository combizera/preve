<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->unsignedInteger('order')->nullable()->after('icon');
        });

        DB::statement(<<<'SQL'
            UPDATE categories AS c
            SET "order" = sub.row_num
            FROM (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY user_id, type ORDER BY id) AS row_num
                FROM categories
            ) AS sub
            WHERE c.id = sub.id
        SQL);

        Schema::table('categories', function (Blueprint $table): void {
            $table->unsignedInteger('order')->nullable(false)->change();
            $table->unique(['user_id', 'type', 'order']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'type', 'order']);
            $table->dropColumn('order');
        });
    }
};
