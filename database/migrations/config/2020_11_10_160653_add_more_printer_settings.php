<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMorePrinterSettings extends Migration
{
    const LABEL_ORIENTATION = [
        'landscape',
        'portrait'
    ]
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_printer_settings', function (Blueprint $table) {
            $table->enum('label_orientation', self::LABEL_ORIENTATION)->default('landscape');
            $table->tinyInt('barcode_width')->default(6);
            $table->integer('barcode_height')->default(250);
            $table->tinyInt('sku_price_font_size')->default(40);
            $table->integer('sku_price_x_position')->default(350);
            $table->integer('sku_price_y_position')->default(255);
            $table->integer('barcode_x_position')->default(65);
            $table->integer('barcode_y_position')->default(35);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_printer_settings', function (Blueprint $table) {
            $table->dropColumn('label_orientation');
            $table->dropColumn('barcode_width');
            $table->dropColumn('barcode_height');
            $table->dropColumn('sku_price_font_size');
            $table->dropColumn('sku_price_x_position');
            $table->dropColumn('sku_price_y_position');
            $table->dropColumn('barcode_x_position');
            $table->dropColumn('barcode_y_position');
        });
    }
}
