<?php

use App\Models\Parts\Part;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveIsTaxableColumnPartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $tableName = Part::getTableName();

        try {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'is_active')) {
                    $table->boolean('is_active')->nullable()->default(false);
                    $table->index(['dealer_id', 'is_active']);
                }

                if (!Schema::hasColumn($tableName, 'is_taxable')) {
                    $table->boolean('is_taxable')->nullable()->default(true);
                }
            });

            if (Schema::hasColumn($tableName, 'is_active')) {
                DB::query()->from($tableName)->whereExists(function ($query) use ($tableName) {
                    $query->select(['part_bin_qty.part_id', 'part_bin_qty.qty'])
                        ->from('part_bin_qty')
                        ->whereColumn('part_bin_qty.part_id', $tableName . '.id')
                        ->where([
                            ['part_bin_qty.qty', '!=', 0],
                        ]);
                })->update([
                    'is_active' => true,
                ]);
            }
        } catch (Exception $exception) {
            $this->info($exception->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(Part::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['dealer_id', 'is_active']);

            $table->dropColumn([
                'is_active',
                'is_taxable',
            ]);
        });
    }
}
