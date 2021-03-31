<?php

declare(strict_types=1);

namespace App\Http\Requests\Bulk\Parts;

use App\Http\Requests\Request;
use App\Repositories\Dms\StockRepository;

class CreateBulkReportRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'required|integer',
            'token' => 'uuid',
            'search_term' => 'string|nullable',
            'type_of_stock' => sprintf(
                'in:%s,%s,%s|nullable',
                StockRepository::STOCK_TYPE_INVENTORIES,
                StockRepository::STOCK_TYPE_PARTS,
                StockRepository::STOCK_TYPE_MIXED
            ),
        ];
    }
}
