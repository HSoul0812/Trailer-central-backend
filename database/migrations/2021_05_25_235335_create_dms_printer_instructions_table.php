<?php

use App\Models\CRM\Dms\Printer\Instructions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDmsPrinterInstructionsTable extends Migration
{
    const PRINTER_FORM_NAME = 'DR2407';

    const PRINTER_FORM_INSTRUCTIONS = [
        // Row 1
        [
            'type' => 'ZPL',
            'name' => 'prev_bos_no',
            'label' => 'Previous Bill of Sale Number',
            'font_size' => 20,
            'x_position' => 1115,
            'y_position' => 75
        ],

        // Row 2
        [
            'type' => 'ZPL',
            'name' => 'dealer_name',
            'label' => 'Print Name of Licensed Colorado Dealer',
            'font_size' => 20,
            'x_position' => 112,
            'y_position' => 205
        ],
        [
            'type' => 'ZPL',
            'name' => 'dealer_no',
            'label' => 'Print Dealer Number',
            'font_size' => 20,
            'x_position' => 945,
            'y_position' => 205
        ],

        // Row 3
        [
            'type' => 'ZPL',
            'name' => 'dealer_address',
            'label' => 'Street Address',
            'font_size' => 20,
            'x_position' => 112,
            'y_position' => 275
        ],
        [
            'type' => 'ZPL',
            'name' => 'dealer_city',
            'label' => 'City',
            'font_size' => 20,
            'x_position' => 628,
            'y_position' => 275
        ],
        [
            'type' => 'ZPL',
            'name' => 'dealer_state',
            'label' => 'State',
            'font_size' => 20,
            'x_position' => 945,
            'y_position' => 275
        ],
        [
            'type' => 'ZPL',
            'name' => 'dealer_zip',
            'label' => 'Zip Code',
            'font_size' => 20,
            'x_position' => 1115,
            'y_position' => 275
        ],

        // Row 4
        [
            'type' => 'ZPL',
            'name' => 'vin',
            'label' => 'Vehicle Identification Number (VIN)',
            'font_size' => 20,
            'x_position' => 1115,
            'y_position' => 275
        ],
        [
            'type' => 'ZPL',
            'name' => 'dealer_address',
            'label' => 'Street Address',
            'font_size' => 20,
            'x_position' => 112,
            'y_position' => 1225
        ],
        [
            'type' => 'ZPL',
            'name' => 'dealer_city',
            'label' => 'City',
            'font_size' => 20,
            'x_position' => 628,
            'y_position' => 1225
        ],
        [
            'type' => 'ZPL',
            'name' => 'dealer_state',
            'label' => 'State',
            'font_size' => 20,
            'x_position' => 945,
            'y_position' => 1225
        ],
        [
            'type' => 'ZPL',
            'name' => 'dealer_zip',
            'label' => 'Zip Code',
            'font_size' => 20,
            'x_position' => 1115,
            'y_position' => 1225
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dms_printer_instructions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('form_id')->index();
            $table->enum('type', Instructions::PRINTER_FORM_TYPES);
            $table->string('name')->index();
            $table->string('label');
            $table->integer('font_size');
            $table->integer('x_position');
            $table->integer('y_position');
            $table->timestamps();

            $table->index(['form_id', 'type'], 'PRINTER_FORM_TYPE');
        });

        // Get Form With Name
        $form = Form::where('name', self::PRINTER_FORM_NAME);
        foreach(self::PRINTER_FORM_INSTRUCTIONS as $instruction) {
            $createdAt = Carbon::now()->setTimezone('UTC')->toDateTimeString();
            DB::table('dms_printer_forms')->insert(array_merge($instruction,
                ['form_id' => $form->id, 'created_at' => $createdAt, 'updated_at' => $createdAt]));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dms_printer_instructions');
    }
}
