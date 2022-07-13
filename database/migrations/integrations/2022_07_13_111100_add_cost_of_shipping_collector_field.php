<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCostOfShippingCollectorField extends Migration
{
    private const TABLE_NAME = 'collector_fields';

    private const FIELD_PARAMS = [
        'field' => 'cost_of_shipping',
        'label' => 'Cost Of Shipping',
        'type' => 'item',
        'mapped' => false,
        'boolean' => false
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!$this->checkField()) {
            DB::transaction(static function () {
                DB::table(self::TABLE_NAME)->insert(self::FIELD_PARAMS);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if ($this->checkField()) {
            DB::table(self::TABLE_NAME)
                ->where('field', self::FIELD_PARAMS['field'])
                ->where('type', self::FIELD_PARAMS['type'])
                ->delete();
        }
    }

    /**
     * @return bool
     */
    private function checkField(): bool
    {
        return DB::table(self::TABLE_NAME)
            ->where('field', self::FIELD_PARAMS['field'])
            ->where('type', self::FIELD_PARAMS['type'])
            ->exists();
    }
}
