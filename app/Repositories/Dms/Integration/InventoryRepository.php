<?php

declare(strict_types=1);

namespace App\Repositories\Dms\Integration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class InventoryRepository implements InventoryRepositoryInterface
{
    public function getAllSince(?string $lastDateSynchronized, int $offSet = 0, int $limit = 0): LazyCollection
    {
        $columns = [
            'inventory_id',
            'dealer_id',
            'dealer_location_id',
            'created_at',
            'updated_at',
            'active',
            'title',
            'stock',
            'manufacturer',
            'brand',
            'model',
            'qb_item_category_id',
            'description',
            'description_html',
            'status',
            'availability',
            'is_consignment',
            'category',
            'video_embed_code',
            'vin',
            'msrp_min',
            'msrp',
            'price',
            'sales_price',
            'use_website_price',
            'website_price',
            'dealer_price',
            'year',
            'condition',
            'length',
            'width',
            'height',
            'weight',
            'gvwr',
            'axle_capacity',
            'show_on_ksl',
            'show_on_racingjunk',
            'is_special',
            'is_featured',
            'latitude',
            'longitude',
            'is_archived',
            'archived_at',
            'broken_video_embed_code',
            'showroom_id',
            'coordinates_updated',
            'payload_capacity',
            'height_display_mode',
            'width_display_mode',
            'length_display_mode',
            'width_inches',
            'height_inches',
            'length_inches',
            'show_on_rvtrader',
            'l_holder',
            'l_attn',
            'l_name_on_account',
            'l_address',
            'l_account',
            'l_city',
            'l_state',
            'l_zip_code',
            'l_payoff',
            'l_phone',
            'l_paid',
            'l_fax',
        ];

        $query = DB::connection('mysql')
            ->table('inventory', 'i')
            ->select($columns);

        if ($lastDateSynchronized) {
            $query->where('i.updated_at_auto', '>=', $lastDateSynchronized);
        }

        if ($offSet) {
            $query->offset($offSet);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->cursor();
    }

    public function getNumberOfRecordsToImport(?string $lastDateSynchronized): int
    {
        $query = DB::connection('mysql')
            ->table('inventory', 'i')
            ->select('inventory_id');

        if ($lastDateSynchronized) {
            $query->where('i.updated_at_auto', '>=', $lastDateSynchronized);
        }

        return $query->count('inventory_id');
    }
}
