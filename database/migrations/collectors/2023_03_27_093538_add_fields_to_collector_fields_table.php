<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFieldsToCollectorFieldsTable extends Migration
{
    private const TABLE = 'collector_fields';

    private const COLLECTOR_FIELDS_DATA = [
        ['field' => 'payload_capacity', 'label' => 'Payload Capacity', 'type' => 'item'],
        ['field' => 'axle_capacity', 'label' => 'Axle Capacity', 'type' => 'item'],
        ['field' => 'overall_length', 'label' => 'Overall Length', 'type' => 'attribute'],
        ['field' => 'external_link', 'label' => 'External Link', 'type' => 'attribute'],
        ['field' => 'min_width', 'label' => 'Min Width', 'type' => 'attribute'],
        ['field' => 'min_height', 'label' => 'Min Height', 'type' => 'attribute'],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = new \DateTime();

        foreach (self::COLLECTOR_FIELDS_DATA as $data) {
            if (DB::table(self::TABLE)->where('field', '=', $data['field'])->exists()) {
                continue;
            }

            $data['updated_at'] = $now;
            $data['created_at'] = $now;

            DB::table(self::TABLE)->insert($data);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (self::COLLECTOR_FIELDS_DATA as $data) {
            DB::table(self::TABLE)->where('field', '=', $data['field'])->delete();
        }
    }
}
