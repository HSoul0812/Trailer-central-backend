<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertPartManufacturers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pdo = DB::connection()->getPdo();
//        $stmt = $pdo->query("SELECT DISTINCT manufacturer FROM `parts` WHERE manufacturer IS NOT NULL AND length(manufacturer) >= 4 AND length(manufacturer) < 50");
//        $insert = $pdo->prepare("INSERT INTO part_manufacturers (name) VALUES (:manufacturer)");
//
//        while($row = $stmt->fetch()) {
//            $insert->execute([
//                'manufacturer' => $row['manufacturer']
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
