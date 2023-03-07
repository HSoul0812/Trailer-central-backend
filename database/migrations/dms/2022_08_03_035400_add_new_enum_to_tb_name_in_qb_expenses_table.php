<?php

use App\Domains\Database\Actions\ModifyEnumColumnAction;
use App\Models\CRM\Dms\Quickbooks\Expense;
use App\Models\CRM\Dms\ServiceOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewEnumToTbNameInQbExpensesTable extends Migration
{
    private $tableName;

    private $columnName;

    public function __construct()
    {
        $this->tableName = Expense::getTableName();
        $this->columnName = 'tb_name';
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
            resolve(ModifyEnumColumnAction::class)
                ->forTable($this->tableName)
                ->forColumn($this->columnName)
                ->withValues(collect(Expense::tbNames()))
                ->execute();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // No need to implement the down method implementation because adding enums doesn't have a side effect
        // the rerun doesn't change anything
    }
}
