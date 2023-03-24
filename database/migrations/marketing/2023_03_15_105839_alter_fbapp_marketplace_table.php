<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterFbappMarketplaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $conn = DB::connection()->getDoctrineConnection();

        Schema::table('fbapp_marketplace', function (Blueprint $table) {
            $table->timestamp('last_attempt_ts')->nullable();
            $table->timestamp('retry_after_ts')->nullable();
        });

        $conn->executeStatement('
            CREATE TRIGGER fbapp_errors_insert AFTER INSERT ON `fbapp_errors` FOR EACH ROW
            UPDATE fbapp_marketplace SET `last_attempt_ts` = NOW(), `retry_after_ts` = NEW.`expires_at` WHERE `id`= NEW.`marketplace_id`
        ');

        $conn->executeStatement('CREATE TRIGGER fbapp_listings_insert AFTER INSERT ON `fbapp_listings` FOR EACH ROW
                UPDATE fbapp_marketplace SET `last_attempt_ts` = NOW() WHERE `id`= NEW.`marketplace_id`
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $conn = DB::connection()->getDoctrineConnection();

        $conn->executeStatement('DROP TRIGGER fbapp_errors_insert');
        $conn->executeStatement('DROP TRIGGER fbapp_listings_insert');

        Schema::table('fbapp_marketplace', function (Blueprint $table) {
            $table->dropColumn('last_attempt_ts');
            $table->dropColumn('retry_after_ts');
        });
    }
}
