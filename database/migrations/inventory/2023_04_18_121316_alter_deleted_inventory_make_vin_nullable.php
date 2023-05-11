<?php

use App\Models\Inventory\DeletedInventory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDeletedInventoryMakeVinNullable extends Migration
{
    private const TABLE_NAME = 'deleted_inventory';
    private const VIN_COLUMN = 'vin';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            if (Schema::hasColumn(self::TABLE_NAME, self::VIN_COLUMN)) {
                $table->string(self::VIN_COLUMN)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            if (
                Schema::hasColumn(self::TABLE_NAME, self::VIN_COLUMN) &&
                !DeletedInventory::whereNull(self::VIN_COLUMN)->exists()
            ) {
                $table->string(self::VIN_COLUMN)->change();
            }
        });
    }
}
