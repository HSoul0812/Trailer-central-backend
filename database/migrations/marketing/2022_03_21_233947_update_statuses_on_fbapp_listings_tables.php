<?php

use App\Models\Marketing\Facebook\Listings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStatusesOnFbappListingsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update Columns
        DB::statement("ALTER TABLE `fbapp_listings` MODIFY COLUMN `status` ENUM('" . implode("', '", Listings::STATUSES) . "')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
