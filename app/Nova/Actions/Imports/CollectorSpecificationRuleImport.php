<?php

namespace App\Nova\Actions\Imports;

use App\Models\Integration\Collector\CollectorSpecificationRule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * Class CollectorSpecificationRuleImport
 * @package App\Nova\Actions\Imports
 */
class CollectorSpecificationRuleImport implements ToModel, WithStartRow
{
    /**
    * @param array $row
    *
    * @return CollectorSpecificationRule
     */
    public function model(array $row): CollectorSpecificationRule
    {
        return new CollectorSpecificationRule([
            'id' => $row[0],
            'collector_specification_id' => $row[1],
            'condition' => $row[2],
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
