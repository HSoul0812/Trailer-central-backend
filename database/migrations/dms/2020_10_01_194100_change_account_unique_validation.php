<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAccountUniqueValidation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qb_accounts', function (Blueprint $table) {
            $table->dropUnique('name');
            $table->unique(['dealer_id', 'name', 'parent_id'], 'unique_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_accounts', function (Blueprint $table) {
            $table->dropUnique('unique_name');
            $table->unique(['dealer_id', 'name'], 'name');
        });
    }
}
