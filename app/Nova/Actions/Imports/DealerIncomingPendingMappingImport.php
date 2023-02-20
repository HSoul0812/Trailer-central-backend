<?php

namespace App\Nova\Actions\Imports;

use Maatwebsite\Excel\Concerns\ToModel;

use App\Models\Feed\Mapping\Incoming\DealerIncomingPendingMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * Class DealerIncomingPendingMappingImport
 * @package App\Nova\Actions\Imports
 */
class DealerIncomingPendingMappingImport implements ToModel, WithStartRow
{
    /**
    * @param array $row
    *
    * @return DealerIncomingPendingMapping
    */
    public function model(array $row): DealerIncomingPendingMapping
    {
        return new DealerIncomingPendingMapping([
            'dealer_id' => $row[0],
            'type'      => $row[1],
            'data'      => $row[2]
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
