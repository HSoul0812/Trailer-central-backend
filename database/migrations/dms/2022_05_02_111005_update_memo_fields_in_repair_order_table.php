<?php

use App\Models\CRM\Dms\ServiceOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMemoFieldsInRepairOrderTable extends Migration
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
        // We need to use DB::statement because we can't use change() with enum
        // Ref: https://stackoverflow.com/questions/33496518/how-to-change-enum-type-column-in-laravel-migration
        Schema::table($this->tableName, function (Blueprint $table) {
            DB::statement("ALTER TABLE $this->tableName CHANGE public_memo public_memo longtext NULL, " .
                "CHANGE private_memo private_memo longtext NULL");
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
            DB::statement("ALTER TABLE $this->tableName CHANGE public_memo public_memo tinytext NULL, " .
                "CHANGE private_memo private_memo tinytext NULL");
        });
    }
}
