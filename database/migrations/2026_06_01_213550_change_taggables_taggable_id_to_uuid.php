<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE taggables ALTER COLUMN taggable_id TYPE uuid USING taggable_id::uuid');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE taggables ALTER COLUMN taggable_id TYPE varchar(255) USING taggable_id::varchar');
    }
};
