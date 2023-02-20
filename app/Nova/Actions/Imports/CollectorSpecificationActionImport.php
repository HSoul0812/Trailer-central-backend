<?php

namespace App\Nova\Actions\Imports;

use App\Models\Integration\Collector\CollectorSpecificationAction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * Class CollectorSpecificationAction
 * @package App\Nova\Actions\Imports
 */
class CollectorSpecificationActionImport implements ToModel, WithStartRow
{
    /**
    * @param array $row
    *
    * @return CollectorSpecificationAction
     */
    public function model(array $row): CollectorSpecificationAction
    {
        return new CollectorSpecificationAction([
            'id' => $row[0],
            'collector_specification_id' => $row[1],
            'action' => $row[2],
            'field' => $row[3],
            'value' => $row[4],
            'created_at' => $row[5],
            'updated_at' => $row[6]
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
