<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuickbookApprovalDeleted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('quickbook_approval_deleted')) {
            Schema::create('quickbook_approval_deleted', function(Blueprint $table)
            {
                $table->bigIncrements('id');
                $table->integer('dealer_id');
                $table->enum('tb_name', [
                    'qb_bills','qb_bill_payment','qb_items','qb_vendors','dealer_employee','qb_invoices','dms_customer','qb_payment','crm_pos_sales','dms_unit_sale','dms_unit_sale_payment','qb_accounts','qb_item_category','qb_payment_methods','qb_journal_entry','qb_expenses','qb_items_new','inventory_floor_plan_payment','dealer_refunds'
                ]);
                $table->integer('tb_primary_id');
                $table->enum('action_type', [
                    'add','update','delete'
                ])->default('add');
                $table->tinyInteger('send_to_quickbook')->default(0);
                $table->text('qb_obj');
                $table->tinyInteger('is_approved')->default(0);
                $table->smallInteger('sort_order')->default(100);
                $table->timestamp('created_at')->useCurrent();
                $table->dateTime('exported_at')->nullable()->default(null);
                $table->integer('qb_id')->nullable()->default(null);
                $table->text('error_result')->nullable();
                $table->integer('removed_by')->nullable()->unsigned()->index();
                $table->dateTime('deleted_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quickbook_approval_deleted');
    }
}
