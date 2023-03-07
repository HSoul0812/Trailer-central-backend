<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixAutoTraderFilters extends Migration
{
    private const INTEGRATION_ID = 68;
    private const TABLE = 'integration';

    private const BROKEN_FILTERS = array(
        array(
            "filter" => array(
                "field" => "category",
                "value" => "horse",
                "operator" => "or"
            ),
            "operator" => "and"
        )
    );

    private const FIXED_FILTERS = array(
        array(
            "filter" => array(
                array(
                    "field" => "category",
                    "value" => "horse",
                    "operator" => "or"
                )
            ),
            "operator" => "and"
        )
    );

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->checkIntegration()) {
            DB::table(self::TABLE)->where('integration_id', self::INTEGRATION_ID)->update([
                'filters' => serialize(self::FIXED_FILTERS),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ($this->checkIntegration()) {
            DB::table(self::TABLE)->where('integration_id', self::INTEGRATION_ID)->update([
                'filters' => serialize(self::BROKEN_FILTERS),
            ]);
        }
    }

    /**
     * @return bool
     */
    private function checkIntegration(): bool {
        $checkIntegration = DB::table(self::TABLE)->where('integration_id', self::INTEGRATION_ID)->exists();

        if ($checkIntegration) {
            return true;
        }
        return false;
    }
}
