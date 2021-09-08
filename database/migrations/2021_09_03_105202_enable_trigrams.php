<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class EnableTrigrams extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * @see https://scoutapm.com/blog/how-to-make-text-searches-in-postgresql-faster-with-trigram-similarity
         */
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');// for enhanced search
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP EXTENSION IF EXISTS pg_trgm');
    }
}
