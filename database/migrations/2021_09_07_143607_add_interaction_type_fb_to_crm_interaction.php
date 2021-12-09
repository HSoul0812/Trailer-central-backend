<?php

use App\Models\CRM\Interactions\Interaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddInteractionTypeFbToCrmInteraction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert CRM Interaction
        DB::statement("ALTER TABLE `crm_interaction`
                        CHANGE `interaction_type` `interaction_type`
                        ENUM('" . implode("','", Interaction::INTERACTION_TYPES) . "')
                        CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
}
