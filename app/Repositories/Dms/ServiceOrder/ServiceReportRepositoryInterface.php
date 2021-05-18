<?php

declare(strict_types=1);

namespace App\Repositories\Dms\ServiceOrder;

use App\Repositories\GenericRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

interface ServiceReportRepositoryInterface extends GenericRepository
{
    /**
     * @param array $params
     * @return LengthAwarePaginator
     * @throws InvalidArgumentException when 'dealer_id' param has not been provided
     */
    public function monthly(array $params): LengthAwarePaginator;
}
