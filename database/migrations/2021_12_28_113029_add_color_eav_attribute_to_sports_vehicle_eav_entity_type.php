<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddColorEavAttributeToSportsVehicleEavEntityType extends Migration
{
    private $entity_type_id;
    private $attribute_id;

    public function __construct()
    {
        $this->entity_type_id = DB::table('eav_entity_type')
            ->where('name', 'sportsvehicle')->value('entity_type_id');

        $this->attribute_id = DB::table('eav_attribute')
            ->where('code', 'color')->value('attribute_id');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('eav_entity_type_attribute')->insert([
            'entity_type_id' => $this->entity_type_id,
            'attribute_id' => $this->attribute_id,
            'sort_order' => 0
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('eav_entity_type_attribute')->where([
            'entity_type_id' => $this->entity_type_id,
            'attribute_id' => $this->attribute_id
        ])->delete();
    }
}
