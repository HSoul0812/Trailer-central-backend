<?php

declare(strict_types=1);

use App\Models\Website\PaymentCalculator\Settings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTheWebsitePaymentCalculatorSettingsTable extends Migration
{
    private $tableName;

    public function __construct()
    {
        $this->tableName = Settings::getTableName();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // No need to run this migration if the table exists in the database already
        if (Schema::hasTable($this->tableName)) {
            return;
        }

        Schema::create($this->tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('website_id');
            $table->integer('entity_type_id');
            $table->enum('inventory_condition', ['used', 'new']);
            $table->integer('months');
            $table->float('apr');
            $table->double('down');
            $table->enum('operator', ['less_than', 'over']);
            $table->double('inventory_price');
            $table->enum('financing', ['no_financing', 'financing']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // To be safe for production database, we won't add the drop table statement here
    }
}
