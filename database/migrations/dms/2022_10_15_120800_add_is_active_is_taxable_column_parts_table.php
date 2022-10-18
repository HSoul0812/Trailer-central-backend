<?php

use App\Models\Parts\Part;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddIsActiveIsTaxableColumnPartsTable extends Migration
{
    private $indexCols = [
        'is_active',
        'is_taxable',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(Part::TABLE_NAME, function (Blueprint $table) {
            $table->boolean('is_active')->nullable()->default(false);
            $table->boolean('is_taxable')->nullable()->default(true);
            $table->index($this->indexCols);
        });

        $query = Part::query()->with(['bins']);

        $query->chunk(1000, function ($parts) {
            foreach ($parts as $part) {
                try {
                    $binsQuantity = $part->bins->pluck('qty');
                    $binsQuantity->each(function ($item, $key) use ($part) {
                        if ($item !== 0) {
                            $part->is_active = true;
                            $part->save();

                            return false;
                        }
                    });
                } catch (Exception $exception) {
                    $this->info($exception->getMessage());
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(Part::TABLE_NAME, function (Blueprint $table) {
            $table->dropIndex($this->indexCols);

            $table->dropColumns($this->indexCols);
        });
    }
}
