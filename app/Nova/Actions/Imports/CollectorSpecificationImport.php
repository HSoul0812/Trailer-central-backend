<?php

namespace App\Nova\Actions\Imports;

use App\Models\Integration\Collector\CollectorSpecification;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * Class CollectorSpecificationImport
 * @package App\Nova\Actions\Imports
 */
class CollectorSpecificationImport implements ToModel, WithStartRow
{
    /**
     * @param array $row
     *
     * @return CollectorSpecification
     */
    public function model(array $row): CollectorSpecification
    {
        return new CollectorSpecification([
            'id' => $row[0],
            'collector_id' => $row[1],
            'logical_operator' => $row[2]
        ]);
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }
}
