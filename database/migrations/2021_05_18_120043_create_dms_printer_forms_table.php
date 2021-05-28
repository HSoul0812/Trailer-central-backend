<?php

use App\Models\CRM\Dms\Printer\Form;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CreateDmsPrinterFormsTable extends Migration
{
    const PRINTER_FORM_DR2407 = [
        'name' => 'DR2407',
        'region' => 'CO',
        'description' => 'Dealer\'s Bill of Sales for a Motor Vehicle',
        'department' => 'Department of Revenue',
        'division' => 'Division of Motor Vehicles',
        'website' => 'www.revenue.state.co.us'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dms_printer_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 25)->index();
            $table->string('region', 3)->index();
            $table->enum('type', Form::FORM_TYPES);
            $table->string('description', 255)->comment('Title of the Form');
            $table->string('department', 255);
            $table->string('division', 255);
            $table->string('website', 255);
            $table->timestamps();
        });

        $createdAt = Carbon::now()->setTimezone('UTC')->toDateTimeString();
        DB::table('dms_printer_forms')->insert(array_merge(self::PRINTER_FORM_DR2407,
                                               ['created_at' => $createdAt, 'updated_at' => $createdAt]));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dms_printer_forms');
    }
}
