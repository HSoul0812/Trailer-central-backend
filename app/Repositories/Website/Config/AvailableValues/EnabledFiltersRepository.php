<?php

declare(strict_types=1);

namespace App\Repositories\Website\Config\AvailableValues;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;

class EnabledFiltersRepository implements AvailableValuesRepositoryInterface
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function pull(int $websiteId): Collection
    {
        return $this->db->table('inventory_filter')
            ->select('attribute')
            ->where('attribute', '!=', 'dealer')
            ->get()
            ->pluck('attribute');
    }
}
