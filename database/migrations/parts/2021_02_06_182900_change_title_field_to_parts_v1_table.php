<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeTitleFieldToPartsV1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $parts = DB::table('parts_v1')->whereNull('title')->get();

        foreach ($parts as $part) {
            DB::table('parts_v1')
                ->where('id', $part->id)
                ->update(['title' => $part->sku]);
        }

        Schema::table('parts_v1', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parts_v1', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
        });
    }
}
