<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleToCollectorField extends Migration
{
    private const TITLE_FIELD =
        [
            'field' => 'title',
            'label' => 'Title',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!$this->fieldExistsOnDb()) {
            DB::table('collector_fields')->insert(self::TITLE_FIELD);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if($this->fieldExistsOnDb()) {
            DB::table('collector_fields')->where('label', self::TITLE_FIELD['label'])->delete();
        }
    }

    public function fieldExistsOnDb() {
        return DB::table('collector_fields')->where('label', self::TITLE_FIELD['label'])->exists();
    }
}
