<?php

use App\Models\Marketing\Facebook\Error;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddExpiresAtToFbappErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fbapp_errors', function (Blueprint $table) {
            $table->boolean('dismissed')->default(false)->after('error_message');
            $table->timestamp('expires_at')->nullable()->index()->after('dismissed');

            // Create Index
            $table->index(['marketplace_id', 'dismissed']);
        });

        // Update Columns
        DB::statement("ALTER TABLE `fbapp_errors` MODIFY COLUMN `error_type` ENUM('" . implode("', '", array_keys(Error::ERROR_TYPES)) . "')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fbapp_errors', function (Blueprint $table) {
            $table->dropIndex('fbapp_errors_marketplace_id_dismissed_index');
            $table->dropColumn('dismissed');
            $table->dropColumn('expires_at');
        });
    }
}
