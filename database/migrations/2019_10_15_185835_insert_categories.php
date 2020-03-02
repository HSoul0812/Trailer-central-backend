<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pdo = DB::connection()->getPdo();
//        $stmt = $pdo->query("SELECT DISTINCT category FROM `parts` WHERE category IS NOT NULL AND length(category) >= 4 AND length(category) < 50");
//        $insert = $pdo->prepare("INSERT INTO part_categories (name) VALUES (:category)");
//
//        while($row = $stmt->fetch()) {
//            $insert->execute([
//                'category' => $row['category']
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
