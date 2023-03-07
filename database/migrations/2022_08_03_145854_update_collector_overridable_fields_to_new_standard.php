<?php

use App\Models\Integration\Collector\Collector;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCollectorOverridableFieldsToNewStandard extends Migration
{

    const OVERRIDABLE_FIELDS = '{"number_batteries":false,"passengers":false,"ac_btu":false,"air_conditioners":false,"available_beds":false,"awning_size":false,"axle_weight":false,"axles":false,"black_water_capacity":false,"brand":false,"cab_type":false,"cargo_weight":false,"type_code":false,"chosen_overlay":false,"color":false,"designation":false,"configuration":false,"construction":false,"conversion":false,"cost_of_shipping":false,"cost_of_unit":false,"created_at":false,"custom_conversion":false,"daily_price":false,"dead_rise":false,"dealer_location":false,"description":false,"draft":false,"drive_trail":false,"dry_weight":false,"electrical_service":false,"engine":false,"engine_hours":false,"engine_size":false,"floorplan":false,"fresh_water_capacity":false,"fuel_capacity":false,"fuel_type":false,"furnace_btu":false,"gray_water_capacity":false,"gvwr":false,"has_stock_images":false,"height":false,"height_display_mode":false,"hidden_price":false,"hitch_weight":false,"horsepower":false,"hull_type":false,"images":false,"interior_color":false,"is_featured":false,"is_rental":false,"is_special":false,"length":false,"length_display_mode":false,"livingquarters":false,"manger":false,"manufacturer":false,"midtack":false,"mileage":false,"model":false,"monthly_payment":false,"msrp":false,"nose_type":false,"note":false,"number_awnings":false,"price":false,"propulsion":false,"pull_type":false,"ramps":false,"roof_type":false,"sales_price":false,"seating_capacity":false,"shortwall_length":false,"show_on_website":false,"showroom_files":false,"side_wall_height":false,"sleeping_capacity":false,"slideouts":false,"stalls":false,"status":false,"stock":false,"tires":false,"title":false,"total_of_cost":false,"total_weight_capacity":false,"transmission":false,"transom":false,"use_website_price":false,"video_embed_code":false,"vin":false,"website_price":false,"weekly_price":false,"weight":false,"wet_weight":false,"width":false,"width_display_mode":false,"year":false}';

    /**
     * Run the migrations.
     * NOTE: This will convert the OLD standard fields to NEW standard, will DELETE old fields that are not collector fields.
     *
     * @return void
     */
    public function up()
    {
        if ($this->checkColumn()) {
            $result = DB::select(
                DB::raw("SELECT id, overridable_fields FROM collector")
            );

            foreach ($result as $item) {
                DB::table('collector')
                    ->where(['id' => $item->id])
                    ->update(['overridable_fields' => $this->transformToNewStandard($item->overridable_fields)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * NOTE: This will convert the NEW standard fields to OLD standard, will NOT recover fields that are not collector fields.
     * @return void
     */
    public function down()
    {
        if ($this->checkColumn()) {
            $result = DB::select(
                DB::raw("SELECT id, overridable_fields FROM collector")
            );

            foreach ($result as $item) {
                DB::table('collector')
                    ->where(['id' => $item->id])
                    ->update(['overridable_fields' => $this->transformToOldStandard($item->overridable_fields)]);
            }
        }
    }


    /**
     * @param $old_fields
     * @return array
     */
    public function transformToNewStandard($old_fields): array
    {
        $old_fields = explode(',', $old_fields);  # ['a','b','c']

        $new_fields = json_decode(self::OVERRIDABLE_FIELDS, true); # ["number_batteries" => false]

        foreach ($new_fields as $field => $bool) {
            if(in_array($field, $old_fields)){
                $new_fields[$field] = true;
            }
        };

        return $new_fields;
    }

    /**
     * @param $new_fields
     * @return string
     */
    public function transformToOldStandard($new_fields): string
    {
        $old_fields = array_keys(array_filter($new_fields, function($v){
            return $v;
        }));

        return implode(",", $old_fields);
    }

    /**
     * Validate column existence on migrate
     * @return bool
     */
    private function checkColumn(): bool
    {
        return Schema::hasColumn('collector', 'overridable_fields');
    }
}
