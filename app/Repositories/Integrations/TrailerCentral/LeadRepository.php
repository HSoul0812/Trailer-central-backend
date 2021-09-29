<?php

declare(strict_types=1);

namespace App\Repositories\Integrations\TrailerCentral;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeadRepository implements LeadRepositoryInterface
{
    private const INVENTORY_TYPE = 'inventory';
    private const IS_NOT_SPAM = 0;

    public function queryAllSince(?string $lastDateSynchronized): Builder
    {
        $columns = array_merge($this->getSerializableColumnsNames(), ['i.manufacturer', 'i.brand', 'i.vin']);

        $query = DB::connection('mysql')
            ->table('website_lead', 'l')
            ->select($columns)
            ->join('inventory as i', 'i.inventory_id', '=', 'l.inventory_id')
            ->where('l.is_spam', self::IS_NOT_SPAM)
            ->where('l.inventory_id', '!=', 0)
            ->where('l.lead_type', self::INVENTORY_TYPE);

        if ($lastDateSynchronized) {
            $query->where('l.date_submitted', '>=', $lastDateSynchronized);
        }

        return $query->orderBy('l.date_submitted');
    }

    public function getSerializableColumnsNames(): array
    {
        return collect(Schema::connection('mysql')
            ->getColumnListing('website_lead'))
            ->map(fn ($column) => "l.$column")
            ->toArray();
    }
}
