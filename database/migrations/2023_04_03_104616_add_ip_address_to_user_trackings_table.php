<?php

use App\Models\UserTracking;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpAddressToUserTrackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_trackings', function (Blueprint $table) {
            $table->string('ip_address', 50)->nullable();
            $table->boolean('location_processed')->default(false);
            $table->string('city', 60)->nullable();
            $table->string('state', 3)->nullable();
            $table->string('country', 2)->nullable();

            $table->index(['ip_address']);
            $table->index(['location_processed']);
        });

        // We don't want to process the records that has ip_address as null
        // basically the records that we inserted to the table before this
        // migration gets deployed
        UserTracking::whereNull('ip_address')->update([
            'location_processed' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_trackings', function (Blueprint $table) {
            $table->dropColumn('ip_address');
            $table->dropColumn('location_processed');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('country');
        });
    }
}
