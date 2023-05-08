<?php

use App\Models\CRM\Report\Report;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;
use Carbon\Carbon;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\Inventory\Attribute;
use App\Models\Inventory\AttributeValue;
use App\Repositories\CRM\Report\ReportRepositoryInterface;

$factory->define(Report::class, function(Faker $faker, array $attributes = []) {

    return [
        'report_name' => $faker->name(),
        'report_type' => $faker->randomElement(Report::REPORT_TYPES),
        'user_id' => $attributes['user_id'],
        'filters' => json_encode([
            'p_start' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'p_end' => Carbon::now()->endOfMonth()->format('Y-m-d'),
            's_start' => Carbon::now()->startOfYear()->format('Y-m-d'),
            's_end' => Carbon::now()->endOfYear()->format('Y-m-d'),
            'chart_span' => $faker->randomElement(['daily', 'monthly']),
            'sales_people' => isset($attributes['sales_people']) ? $faker->randomElements($attributes['sales_people']) : null,
            'lead_status' => $faker->randomElements(array_keys(LeadStatus::PUBLIC_STATUSES), 2),
            'lead_source' => $faker->company(),
        ])
    ];
});

$factory->define(AttributeValue::class, function (Faker $faker, array $params = []) {

    // get appropriate $attribute
    $attributeQuery = Attribute::query();

    if (isset($params['attribute_id'])) {
        $attributeQuery = $attributeQuery->where('attribute_id', $params['attribute_id']);
    } else {

        $attributeQuery = $attributeQuery->where('code', '<>', ReportRepositoryInterface::INVENTORY_ATTRIBUTE_PULL_TYPE_CODE);
            
        $attributeQuery = $attributeQuery->inRandomOrder();
    }

    // build $attributeValues
    $attribute = $attributeQuery->first();
    $attributeValuesRaw = $attribute->values;
    $attributeValues = [];

    if ($attributeValuesRaw) {

        $attributeValuesArray = explode(',', $attributeValuesRaw);
        foreach ($attributeValuesArray as $attributeValueRaw) {
            
            $attributeValueRawArray = explode(':', $attributeValueRaw);

            if (count($attributeValueRawArray) > 1) {
                list($attributeValueKey, $attributeValueValue) = $attributeValueRawArray;
                $attributeValues[$attributeValueKey] = $attributeValueValue;
            }
        }
    }

    return [
        'attribute_id' => $attribute->attribute_id,
        'inventory_id' => $params['inventory_id'],
        'value' => $attributeValues ? $faker->randomElement(array_keys($attributeValues)) : null,
    ];
});