<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateConstructionAttributeValues extends Migration
{

    private const ATTRIBUTE_CODE = 'construction';
    private const ACTUAL_VALUES = 'aluminum:Aluminum,steel_frame_aluminum:Steel Frame / Aluminum Skin,composite:Composite,steel:Steel,galvanized:Galvanized';
    private const UPDATED_VALUES = 'aluminum:Aluminum,steel_frame_aluminum:Steel Frame / Aluminum Skin,composite:Composite,fiberglass:Fiberglass,steel:Steel,galvanized:Galvanized,hypalon:Hypalon,roplene:Roplene,wood:Wood,other:Other';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $attribute = DB::table('eav_attribute')->where('code', self::ATTRIBUTE_CODE);

        if ($attribute->exists()) {
            DB::transaction(static function () use ($attribute) {
                $attribute->update(['values' => self::UPDATED_VALUES]);
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
        DB::table('eav_attribute')->where('code', self::ATTRIBUTE_CODE)->update(['values' => self::ACTUAL_VALUES]);
    }
}
