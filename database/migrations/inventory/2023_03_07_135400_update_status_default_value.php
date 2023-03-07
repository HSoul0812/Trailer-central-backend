<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStatusDefaultValue extends Migration
{
    private const INVENTORY_TABLE = 'inventory';
    private const INVENTORY_COLUMN = 'status';

    /**
     * Run the migrations.
     * Prevent Collectors and other routines fail add default value to status
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::INVENTORY_TABLE, function (Blueprint $table) {
            $table->integer(self::INVENTORY_COLUMN, 255)->default(1)->change();
        });
    }
}
