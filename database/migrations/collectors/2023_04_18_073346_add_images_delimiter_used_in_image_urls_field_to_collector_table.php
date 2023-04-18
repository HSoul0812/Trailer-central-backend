<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddImagesDelimiterUsedInImageUrlsFieldToCollectorTable extends Migration
{
    private const TABLE = 'collector';
    private const FIELD = 'images_delimiter_used_in_image_url';
    private const DEALER_ID = 6223;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn(self::TABLE, self::FIELD)) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->boolean(self::FIELD)->default(false);
            });
        }

        DB::table(self::TABLE)->where('dealer_id', '=', self::DEALER_ID)->update([self::FIELD => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropColumn(self::FIELD);
        });
    }
}
