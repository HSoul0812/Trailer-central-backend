<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCollectorFieldsTable extends Migration
{
    private const COLLECTOR_FIELDS = [
        [
            'field' => 'dealer_location',
            'label' => 'Dealer Location',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'type_code',
            'label' => 'Category',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'status',
            'label' => 'Status',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'msrp',
            'label' => 'MSRP',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'use_website_price',
            'label' => 'Use Website Price',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'price',
            'label' => 'Price',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'sales_price',
            'label' => 'Sales Price',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'website_price',
            'label' => 'Website Price',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'total_of_cost',
            'label' => 'Total Of Cost',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'cost_of_unit',
            'label' => 'Cost Of Unit',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'designation',
            'label' => 'Condition',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'brand',
            'label' => 'Brand',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'manufacturer',
            'label' => 'Manufacturer',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'created_at',
            'label' => 'Created At',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'stock',
            'label' => 'Stock',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'model',
            'label' => 'Model',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'width',
            'label' => 'Width',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'height',
            'label' => 'Height',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'length',
            'label' => 'Length',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'year',
            'label' => 'Year',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'showroom_files',
            'label' => 'Showroom Files',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'monthly_payment',
            'label' => 'Monthly Payment',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'height_display_mode',
            'label' => 'Height Display Mode',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'length_display_mode',
            'label' => 'Length Display Mode',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'width_display_mode',
            'label' => 'Width Display Mode',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'chosen_overlay',
            'label' => 'Chosen Overlay',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'is_special',
            'label' => 'Is Special',
            'type' => 'item',
            'boolean' => true,
            'mapped' => false
        ],
        [
            'field' => 'hidden_price',
            'label' => 'Hidden Price',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'images',
            'label' => 'Images',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'weight',
            'label' => 'Weight',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'gvwr',
            'label' => 'GVWR',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'video_embed_code',
            'label' => 'Video Embed Code',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'is_featured',
            'label' => 'Is Featured',
            'type' => 'item',
            'boolean' => true,
            'mapped' => false
        ],
        [
            'field' => 'description',
            'label' => 'Description',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'has_stock_images',
            'label' => 'Has Stock Images',
            'type' => 'item',
            'boolean' => true,
            'mapped' => false
        ],
        [
            'field' => 'vin',
            'label' => 'Vin',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'note',
            'label' => 'Note',
            'type' => 'item',
            'boolean' => false,
            'mapped' => false
        ],
        [
            'field' => 'show_on_website',
            'label' => 'Show On Website',
            'type' => 'item',
            'boolean' => true,
            'mapped' => false
        ],
    ];

    private const MAPPED_ATTRIBUTES = [
        'pull_type',
        'fuel_type',
        'nose_type',
        'color'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('collector_fields');

        Schema::create('collector_fields', function (Blueprint $table) {
            $table->increments('id');

            $table->string('field', 128)->unique();
            $table->string('label', 128)->unique();
            $table->string('type', 128);

            $table->boolean('mapped')->default(false);
            $table->boolean('boolean')->default(false);

            $table->timestamps();
        });

        $attributes = [];

        foreach (DB::table('eav_attribute')->select(['code', 'name'])->get() as $attribute) {
            $fieldsAttribute = [
                'field' => $attribute->code,
                'label' => $attribute->name,
                'type' => 'attribute',
                'boolean' => false,
                'mapped' => false
            ];

            if (in_array($fieldsAttribute['field'], self::MAPPED_ATTRIBUTES)) {
                $fieldsAttribute['mapped'] = true;
            }

            $attributes[] = $fieldsAttribute;
        }

        DB::table('collector_fields')->insert(array_merge(self::COLLECTOR_FIELDS, $attributes));

        exit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
