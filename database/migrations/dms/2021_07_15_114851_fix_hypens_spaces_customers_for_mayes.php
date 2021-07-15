<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixHypensSpacesCustomersForMayes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * This raw query updates hypen dash on the firstnames and move correct values to display_name and company_name
         * for the tasks PRTBND-873
         */

        DB::statement("UPDATE `dms_customer` SET
        display_name = CASE WHEN length(trim(REPLACE(display_name, '-', ''))) > 0 THEN
            trim(REPLACE(display_name, '-', ''))
        WHEN (trim(REPLACE(display_name, '-', '')) = NULL
            OR trim(REPLACE(display_name, '-', '')) = '')
            and(length(trim(REPLACE(first_name, '-', ''))) > 2
                AND length(trim(REPLACE(last_name, '-', ''))) > 2) THEN
            CONCAT(first_name, ' ', last_name)
        ELSE
            'n/a'
        END,
        company_name = CASE WHEN length(trim(REPLACE(display_name, '-', ''))) > 0 THEN
            trim(REPLACE(display_name, '-', ''))
        WHEN (trim(REPLACE(display_name, '-', '')) = NULL
            OR trim(REPLACE(display_name, '-', '')) = '')
            and(length(trim(REPLACE(first_name, '-', ''))) > 2
                AND length(trim(REPLACE(last_name, '-', ''))) > 2) THEN
            NULL
        ELSE
            'n/a'
        END,
        first_name = CASE WHEN ((LOCATE('-', first_name) = 0) = 0)
            OR length(trim(first_name)) = 0 THEN
            NULL
        ELSE
            trim(first_name)
        END,
        last_name = CASE WHEN ((LOCATE('-', first_name) = 0) = 0)
            OR length(trim(first_name)) = 0 THEN
            NULL
        ELSE
            trim(last_name)
        END
    WHERE
        `display_name` LIKE '-%' OR `first_name` LIKE '-%';");

    }


}
