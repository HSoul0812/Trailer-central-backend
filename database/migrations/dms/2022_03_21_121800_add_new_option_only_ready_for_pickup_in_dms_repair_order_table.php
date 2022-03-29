<?php

use App\Models\CRM\Dms\ServiceOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewOptionOnlyReadyForPickupInDmsRepairOrderTable extends Migration
{
    private $tableName;

    public function __construct()
    {
        $this->tableName = ServiceOrder::getTableName();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // We need to use DB::statement because we can't use change() with enum
            // Ref: https://stackoverflow.com/questions/33496518/how-to-change-enum-type-column-in-laravel-migration
            DB::statement("ALTER TABLE $this->tableName MODIFY COLUMN status ENUM('picked_up','ready_for_pickup','on_tech_clipboard','waiting_custom','waiting_parts','warranty_processing','quote','work_available', 'closed_quote', '" . ServiceOrder::STATUS_ONLY_READY_FOR_PICK_UP ."') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            DB::statement("ALTER TABLE $this->tableName MODIFY COLUMN status ENUM('picked_up','ready_for_pickup','on_tech_clipboard','waiting_custom','waiting_parts','warranty_processing','quote','work_available', 'closed_quote') NOT NULL");
        });
    }
}
