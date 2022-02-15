<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddWeeklyDailyRentalSportVehicle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rentalAttribute = DB::table("eav_attribute")->where("code","is_rental")->first();
        $type = DB::table("eav_entity_type")->where("name", "sportsvehicle")->first();
        if(!empty($rentalAttribute) && !empty($type)) {
            $rental = DB::table("eav_entity_type_attribute")->where("entity_type_id", $type->entity_type_id)
                ->where("attribute_id", $rentalAttribute->attribute_id)
                ->first();

            $weeklyAttribute = DB::table("eav_attribute")->where("code","weekly_price")->first();
            if(!empty($weeklyAttribute) && !empty($rental)) {
                $weeklyRental = DB::table("eav_entity_type_attribute")->where("entity_type_id", $type->entity_type_id)
                    ->where("attribute_id", $weeklyAttribute->attribute_id)
                    ->first();
                if(empty($weeklyRental)) {
                    DB::table("eav_entity_type_attribute")->insert([
                        "entity_type_id" => $type->entity_type_id,
                        "attribute_id" => $weeklyAttribute->attribute_id,
                        "sort_order" => $rental->sort_order + 1,
                    ]);
                }
            }
            $dailyAttribute = DB::table("eav_attribute")->where("code","daily_price")->first();
            if(!empty($dailyAttribute) && !empty($rental)) {
                $dailyRental = DB::table("eav_entity_type_attribute")->where("entity_type_id", $type->entity_type_id)
                    ->where("attribute_id", $dailyAttribute->attribute_id)
                    ->first();
                if(empty($dailyRental)) {
                    DB::table("eav_entity_type_attribute")->insert([
                        "entity_type_id" => $type->entity_type_id,
                        "attribute_id" => $dailyAttribute->attribute_id,
                        "sort_order" => $rental->sort_order + 2,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $type = DB::table("eav_entity_type")->where("name", "sportsvehicle")->first();
        $weeklyAttribute = DB::table("eav_attribute")->where("code","weekly_price")->first();
        $dailyAttribute = DB::table("eav_attribute")->where("code","daily_price")->first();

        DB::table("eav_entity_type_attribute")->where("entity_type_id", $type->id)
            ->where("attribute_id", $weeklyAttribute->attribute_id)->delete();
        DB::table("eav_entity_type_attribute")->where("entity_type_id", $type->id)
            ->where("attribute_id", $dailyAttribute->attribute_id)->delete();
    }
}
