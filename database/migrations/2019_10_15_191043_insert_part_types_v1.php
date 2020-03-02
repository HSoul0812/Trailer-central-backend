<?php

use Illuminate\Database\Migrations\Migration;

class InsertPartTypesV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pdo = DB::connection()->getPdo();

//        $pdo->query("INSERT INTO part_types (name) VALUES ('Audio and Video')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Awnings')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Coatings and Cleaners')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Housewares')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('LP Gas')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Major Appliances')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Outdoor / Recreational Gear')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Plumbing')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Axle Assemblies')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Axle Components')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Brake Assemblies')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Hardware')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Suspensions')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Tires')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Wheels')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Jacks & Couplers')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Towing Accessories & Safety')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Ramps, Gates and Doors')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Vents & Trim')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Body and Frame Components')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Fenders, Sides & Rails')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Paint and Decals')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Electrical & Lights')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Hydraulics')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('RV Accessories')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Sanitation')");
//        $pdo->query("INSERT INTO part_types (name) VALUES ('Misc')");
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
