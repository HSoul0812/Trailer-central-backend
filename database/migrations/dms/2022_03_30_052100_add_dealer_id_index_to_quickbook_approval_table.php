<?php

use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealerIdIndexToQuickbookApprovalTable extends Migration
{
    private $tableName;

    public function __construct()
    {
        $this->tableName = QuickbookApproval::getTableName();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->index(['dealer_id']);
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
            $table->dropIndex(['dealer_id']);
        });
    }
}
