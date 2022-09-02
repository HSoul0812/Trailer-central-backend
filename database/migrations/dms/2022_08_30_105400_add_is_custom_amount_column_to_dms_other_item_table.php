<?php

use App\Models\CRM\Dms\ServiceOrder\OtherItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsCustomAmountColumnToDmsOtherItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(OtherItem::TABLE_NAME, function (Blueprint $table) {
            $table->boolean('is_custom_amount')->after('taxable')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(OtherItem::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('is_custom_amount');
        });
    }
}
