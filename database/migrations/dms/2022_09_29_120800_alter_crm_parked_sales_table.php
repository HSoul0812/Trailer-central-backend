<?php

use App\Models\Pos\ParkedSale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCrmParkedSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(ParkedSale::getTableName(), function (Blueprint $table) {
            $table->decimal('discount', 10, 2)->default(0)->after('customer_id');
            $table->decimal('shipping', 10, 2)->default(0)->after('customer_id');
            $table->unsignedInteger('sales_person_id')->nullable()->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(ParkedSale::getTableName(), function (Blueprint $table) {
            $table->dropColumns([
                'discount',
                'shipping',
                'sales_person_id',
            ]);
        });
    }
}
