<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CRM\Leads\LeadAssign;

class CreateCrmLeadAssignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crm_lead_assign', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('dealer_id'); // int(11) NOT NULL,

            $table->integer('lead_id'); // int(11) NOT NULL,

            $table->integer('dealer_location_id')->nullable(); // int(11) DEFAULT NULL,

            $table->string('salesperson_type'); // string(255) NOT NULL,

            $table->integer('found_salesperson_id'); // int(11) NOT NULL,

            $table->integer('chosen_salesperson_id'); // int(11) NOT NULL,

            $table->enum('assigned_by', LeadAssign::ASSIGNED_BY_TYPES); // enum('autoassign', 'hotpotato', 'dealer', 'salesperson') NOT NULL,

            $table->integer('assigned_by_id')->nullable(); // int(11) DEFAULT NULL sales person ID if sales person chosen as "assigned by"

            $table->enum('status', LeadAssign::ASSIGNED_STATUS); // enum('assigned', 'emailed', 'skipped', 'error') NOT NULL,

            $table->text('explanation'); // text() NOT NULL

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_lead_assign');
    }
}
