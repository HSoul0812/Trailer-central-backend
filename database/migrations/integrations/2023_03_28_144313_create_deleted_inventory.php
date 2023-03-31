<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeletedInventory extends Migration
{
    private const TABLE_NAME = 'deleted_inventory';
    private const VIN_COLUMN = 'vin';
    private const DEALER_ID = 'dealer_id';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            if (!$this->checkColumn(self::VIN_COLUMN)) {
                $table->string(self::VIN_COLUMN);
            }

            if (!$this->checkColumn(self::DEALER_ID)) {
                $table->integer(self::DEALER_ID);
            }

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }

    /**
     * Validate column existence on migrate
     * @param string $column
     * @return bool
     */
    private function checkColumn(string $column): bool
    {
        return Schema::hasColumn(self::TABLE_NAME, $column);
    }
}
