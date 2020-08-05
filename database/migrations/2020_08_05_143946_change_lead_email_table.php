<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Inventory\Inventory;

class ChangeLeadEmailTable extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void 
     */
    public function up()
    {
        Schema::table('lead_email', function (Blueprint $table) {
            $table->text('email');
            $table->text('cc_email')->nullable();    
            $table->integer('dealer_location_id')->nullable();
        });
        
        Schema::table('lead_email', function (Blueprint $table) { 
            $table->index('dealer_location_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('lead_email', function (Blueprint $table) {
            $table->string('email');
            $table->dropColumn('cc_email');
            $table->dropColumn('dealer_location_id');
        });

    }
}
