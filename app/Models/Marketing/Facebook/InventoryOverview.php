<?php

namespace App\Models\Marketing\Facebook;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class InventoryOverview extends Model
{
    use TableAware;

    /**
     * Table name
     *
     * @var string
     */
    const TABLE_NAME = 'fme_inventory_overview';

    /**
     * Types of overview records in view.
     *
     * @var array
     */
    const OVERVIEW_TYPES = [
        'inventory',
        'listing',
        'error'
    ];

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * Get the query to generate an overview report for a given integration.
     * @param $id
     * @return Builder
     */
    public static function getAllByIntegrationId($id): Builder
    {
        return DB::table(self::TABLE_NAME)
            ->orderBy('created_at', 'desc')
            ->where('marketplace_id', $id);
    }
}
