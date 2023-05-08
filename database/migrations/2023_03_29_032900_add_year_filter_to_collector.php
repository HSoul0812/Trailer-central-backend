<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYearFilterToCollector extends Migration
{
    private $table = 'collector';
    private $columnYear = 'factory_mapping_filter_year_from';
    private $columnSkip = 'factory_mapping_filter_skip_units';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasColumn($this->table, $this->columnYear)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->addColumn('smallInteger', $this->columnYear, ['length' => 4])->nullable();
                $table->addColumn('tinyInteger', $this->columnSkip, ['length' => 1])->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn($this->table, $this->columnYear)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn($this->columnYear);
                $table->dropColumn($this->columnSkip);
            });
        }
    }
}
