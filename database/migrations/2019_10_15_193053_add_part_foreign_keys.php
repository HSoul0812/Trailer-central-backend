<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $pdo = DB::connection()->getPdo();
//        $pdo->query("ALTER TABLE parts_v1 MODIFY COLUMN dealer_id int(11) UNSIGNED NOT NULL;");
//        $pdo->query("ALTER TABLE parts_v1 MODIFY COLUMN manufacturer_id BIGINT(20) UNSIGNED;");
//        $pdo->query("ALTER TABLE parts_v1 MODIFY COLUMN brand_id BIGINT(20) UNSIGNED;");
//        $pdo->query("ALTER TABLE parts_v1 MODIFY COLUMN type_id BIGINT(20) UNSIGNED;");
//        $pdo->query("ALTER TABLE parts_v1 MODIFY COLUMN category_id BIGINT(20) UNSIGNED;");
//        $pdo->query("ALTER TABLE parts_v1 MODIFY COLUMN vendor_id int(10) UNSIGNED;");
//
//
//        Schema::table('parts_v1', function (Blueprint $table) {
//            $table->foreign('dealer_id')
//                    ->references('dealer_id')
//                    ->on('dealer')
//                    ->onDelete('CASCADE')
//                    ->onUpdate('CASCADE');
//
//            $table->foreign('vendor_id')
//                    ->references('id')
//                    ->on('qb_vendors')
//                    ->onDelete('SET NULL')
//                    ->onUpdate('CASCADE');
//
//            $table->foreign('manufacturer_id')
//                    ->references('id')
//                    ->on('part_manufacturers')
//                    ->onDelete('SET NULL')
//                    ->onUpdate('CASCADE');
//
//            $table->foreign('brand_id')
//                    ->references('id')
//                    ->on('part_brands')
//                    ->onDelete('SET NULL')
//                    ->onUpdate('CASCADE');
//
//            $table->foreign('type_id')
//                    ->references('id')
//                    ->on('part_types')
//                    ->onDelete('SET NULL')
//                    ->onUpdate('CASCADE');
//
//            $table->foreign('category_id')
//                    ->references('id')
//                    ->on('part_categories')
//                    ->onDelete('SET NULL')
//                    ->onUpdate('CASCADE');
//        });
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
