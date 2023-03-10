<?php

namespace App\Nova\Actions\Imports;

use Maatwebsite\Excel\Concerns\ToModel;

use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping;

/**
 * Class DefaultValueMappingImport
 * @package App\Nova\Actions\Imports
 */
class DefaultValueMappingImport implements ToModel, WithStartRow
{
    /**
    * @param array $row
    *
    * @return DealerIncomingMapping
    */
    public function model(array $row): DealerIncomingMapping
    {
        return new DealerIncomingMapping([
            'dealer_id' => $row[0],
            'map_from'  => $row[1],
            'map_to'    => $row[2],
            'type'      => $row[3]
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
