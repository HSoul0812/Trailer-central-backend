<?php

namespace App\Models\Marketing\Facebook;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class PostingHistory extends Model
{
    use TableAware;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'record_id';

    /**
     * Table name
     *
     * @var string
     */
    const TABLE_NAME = 'fme_posting_history';

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
    public static function getAllByIntegrationIdQuery($id): Builder
    {
        return DB::table(self::TABLE_NAME)
            ->orderBy('created_at', 'DESC')
            ->where('marketplace_id', $id);
    }

    /**
     * Get the query to generate an overview report for all integrations.
     * @return Builder
     */
    public static function getAllQuery(): Builder
    {
        return DB::table(self::TABLE_NAME)
            ->orderBy('created_at', 'DESC')
            ->select('*');
    }
}
