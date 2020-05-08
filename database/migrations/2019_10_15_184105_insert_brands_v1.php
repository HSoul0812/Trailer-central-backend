<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertBrandsV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pdo = DB::connection()->getPdo();
//        $stmt = $pdo->query("SELECT DISTINCT brand FROM `parts` WHERE brand IS NOT NULL AND length(brand) >= 4;");
//        $insert = $pdo->prepare("INSERT INTO part_brands (name) VALUES (:brand)");
//
//        while($row = $stmt->fetch()) {
//            $insert->execute([
//                'brand' => $row['brand']
//            ]);
//        }
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
