<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFieldFieldToPartsFilterTable extends Migration
{
    private const FIELD_MAPPING = [
        'manufacturer' => 'manufacturer_id',
        'type' => 'type_id',
        'price' => 'price',
        'brand' => 'brand_id',
        'description' => 'search_term',
        'sku' => 'sku',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'category' => 'category_id',
        'subcategory' => 'subcategory'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parts_filter', function (Blueprint $table) {
            $table->string('field', 255)->after('label')->nullable();
        });

        $filters = DB::table('parts_filter')->select()->get();

        foreach ($filters as $filter) {
            $field = self::FIELD_MAPPING[$filter->attribute];

           DB::table('parts_filter')
               ->where('id', $filter->id)
               ->update(['field' => $field]);
        }

        Schema::table('parts_filter', function (Blueprint $table) {
            $table->string('field', 255)->nullable(false)->change();;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parts_filter', function (Blueprint $table) {
            $table->dropColumn('field');
        });
    }
}
