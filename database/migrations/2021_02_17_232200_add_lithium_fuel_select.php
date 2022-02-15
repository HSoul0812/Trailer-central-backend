<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLithiumFuelSelect extends Migration
{
    public function up() {
        $fuelType = DB::table("eav_attribute")->where("code", "fuel_type")->first();
        $types = explode(',', $fuelType->values);
        if(!in_array('lithium:Lithium',$types)) {
            $types[] = 'lithium:Lithium';
            $values = implode(',',$types);
            DB::table("eav_attribute")->where("code", "fuel_type")->update([
                "values" => $values
            ]);
        }
    }

    public function down() {
        $fuelType = DB::table("eav_attribute")->where("code", "fuel_type")->first();
        $types = explode(',', $fuelType->values);
        if(in_array('lithium:Lithium',$types)) {
            foreach ($types as $key => $type) {
                if($type == 'lithium:Lithium') {
                    unset($types[$key]);break;
                }
            }
            $values = implode(',',$types);
            DB::table("eav_attribute")->where("code", "fuel_type")->update([
                "values" => $values
            ]);
        }
    }
}
