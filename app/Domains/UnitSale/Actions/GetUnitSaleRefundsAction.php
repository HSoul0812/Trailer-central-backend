<?php

namespace App\Domains\UnitSale\Actions;

use App\Domains\Shared\Traits\WhenAble;
use App\Models\CRM\Dms\Refund;
use App\Models\CRM\Dms\UnitSale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class GetUnitSaleRefundsAction
{
    use WhenAble;

    /** @var int */
    private $perPage = 10;

    /** @var int */
    private $page = 1;

    /** @var array */
    private $relations = [];

    /** @var string */
    private $sort = '-created_at';

    /**
     * Fetch the refunds Collection
     *
     * @param int $unitSaleId
     * @return LengthAwarePaginator
     */
    public function execute(int $unitSaleId): LengthAwarePaginator
    {
        $dealerId = UnitSale::findOrFail($unitSaleId, ['dealer_id'])->dealer_id;

        return Refund::query()
            ->with($this->relations)
            ->where('dealer_id', $dealerId)
            ->where(function (Builder $builder) use ($unitSaleId) {
                $builder
                    ->where(function (Builder $builder) use ($unitSaleId) {
                        $builder
                            ->where('tb_name', UnitSale::getTableName())
                            ->where('tb_primary_id', $unitSaleId);
                    })
                    ->orWhereHas('invoice', function (Builder $builder) use ($unitSaleId) {
                        $builder->where('unit_sale_id', $unitSaleId);
                    });
            })
            ->orderBy(...$this->orderBy())
            ->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    /**
     * @param int $perPage
     * @return GetUnitSaleRefundsAction
     */
    public function withPerPage(int $perPage): GetUnitSaleRefundsAction
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * @param int $page
     * @return GetUnitSaleRefundsAction
     */
    public function withPage(int $page): GetUnitSaleRefundsAction
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param array $relations
     * @return GetUnitSaleRefundsAction
     */
    public function withRelations(array $relations): GetUnitSaleRefundsAction
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * @param string $sort
     * @return GetUnitSaleRefundsAction
     */
    public function withSort(string $sort): GetUnitSaleRefundsAction
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get the orderBy clause
     * @return array
     */
    private function orderBy(): array
    {
        $direction = $this->sort[0] === '-' ? 'desc' : 'asc';

        $column = str_replace('-', '', $this->sort);

        return [$column, $direction];
    }
}
