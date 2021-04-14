<?php

use App\Models\Website\Forms\FieldMap;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeadTypeNonLead extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update Mappable Fields For Form Field Map
        DB::statement("ALTER TABLE website_form_field_map MODIFY COLUMN map_field ENUM('" . implode("', '", array_keys(FieldMap::getNovaMapFields())) . "')");

        // Mark Vendor as Non-Lead
        DB::table('website_form_field_map')->insertOrIgnore([
            ['type' => 'lead_type', 'form_field' => 'vendor', 'map_field' => 'nonlead', 'db_table' => 'website_lead', 'details' => 'Non-leads will NOT be inserted into the DB.']
        ]);
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
