<?php

declare(strict_types=1);

namespace App\Repositories\Dms\ServiceOrder;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\CRM\Dms\ServiceOrder\MonthlyServiceHours;
use Illuminate\Database\Query\Processors\Processor;
use App\Repositories\Traits\SortTrait;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use stdClass;

class ServiceReportRepository implements ServiceReportRepositoryInterface
{
    use SortTrait;

    private $sortOrders = [
        'month_name' => [
            'field' => 'month_name',
            'direction' => 'DESC'
        ],
        '-month_name' => [
            'field' => 'month_name',
            'direction' => 'ASC'
        ],
        'type' => [
            'field' => 'type',
            'direction' => 'DESC'
        ],
        '-type' => [
            'field' => 'type',
            'direction' => 'ASC'
        ],
        'unit_price' => [
            'field' => 'unit_price',
            'direction' => 'DESC'
        ],
        '-unit_price' => [
            'field' => 'unit_price',
            'direction' => 'ASC'
        ]
    ];

    /**
     * @param array $params
     * @return LengthAwarePaginator
     * @throws InvalidArgumentException when 'dealer_id' param has not been provided
     */
    public function monthly(array $params):LengthAwarePaginator
    {
        if (empty($params['dealer_id'])) {
            throw new InvalidArgumentException("'dealer_id' param is required");
        }

        $query = DB::table('dms_service_hrs_report');

        $params['per_page'] = $params['per_page'] ?? 15;

        $query->where('dealer_id', $params['dealer_id']);

        if (isset($params['search_term'])) {
            $term = $params['search_term'];

            $query->where(static function ($subQuery) use ($term): void {
                $subQuery->where('month_name', 'LIKE', "%{$term}%");
            });
        }

        $query->processor = $this->getProcessor();

        if (isset($params['sort'])) {
            $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    protected function getSortOrders(): array
    {
        return $this->sortOrders;
    }

    /**
     * Provides a processor for the query builder
     *
     * @return Processor
     */
    private function getProcessor(): Processor
    {
        return new class extends Processor {
            /**
             * Query model hydration
             *
             * @param Builder $query
             * @param array $results
             * @return array
             */
            public function processSelect(Builder $query, $results): array
            {
                return collect($results)->map(static function (stdClass $record) {
                    return MonthlyServiceHours::from($record);
                })->toArray();
            }
        };
    }
}
