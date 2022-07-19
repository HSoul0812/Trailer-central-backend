<?php

namespace App\Domains\UnitSale\Actions;

use App\Domains\Shared\Traits\WhenAble;
use App\Models\CRM\Dms\Refund;
use App\Models\CRM\Dms\UnitSale;
use Carbon\Carbon;
use Exception;
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

    /** @var int|null */
    private $registerId;

    /** @var int|null */
    private $customerId;

    /** @var array */
    private $createdAtBetween = [];

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

            // Normal filter fields, we only add the where clause if
            // they're not empty
            ->when(!empty($this->registerId), function (Builder $builder) {
                $builder->where('register_id', $this->registerId);
            })
            ->when(!empty($this->createdAtBetween), function (Builder $builder) {
                $builder->whereBetween('created_at', $this->createdAtBetween);
            })

            // The main condition to match the unit sale id, here we try to
            // match the refund where the invoice for the refund has the unit same
            // unit sale with the one that's provided in this method
            ->where(function (Builder $builder) use ($unitSaleId) {
                $builder
                    // We start by looking at the refund table, match the tb_name
                    // and tb_primary_id to fund the matched unit sale id
                    ->where(function (Builder $builder) use ($unitSaleId) {
                        $builder
                            ->where('tb_name', UnitSale::getTableName())
                            ->where('tb_primary_id', $unitSaleId);
                    })

                    // OR, we'll look for the one that has invoice with the matched unit_sale_id
                    // this is usual for those refund that has qb_payment as a tb_name
                    ->orWhereHas('invoice', function (Builder $builder) use ($unitSaleId) {
                        $builder->where('unit_sale_id', $unitSaleId);
                    });
            })

            // If the customerId isn't empty, we will find it from the invoice
            // table or the unit_sale table
            ->when(!empty($this->customerId), function (Builder $builder) use ($dealerId) {
                $builder->where(function (Builder $builder) use ($dealerId) {
                    $builder
                        // We start by looking at the invoice and find the invoice that has this customer
                        ->whereHas('invoice', function (Builder $builder) {
                            $builder->where('customer_id', $this->customerId);
                        })

                        // OR, we will look into the dms_unit_sale table, but we also need to filter
                        // by dealer_id too, so we don't accidentally pick other dealer unit_sale 
                        ->orWhereHas('unitSale', function (Builder $builder) use ($dealerId) {
                            $builder
                                ->where('dealer_id', $dealerId)
                                ->where('buyer_id', $this->customerId);
                        });
                });
            })

            // Order and paginate
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
     * @param int|null $registerId
     * @return GetUnitSaleRefundsAction
     */
    public function withRegisterId(?int $registerId): GetUnitSaleRefundsAction
    {
        $this->registerId = $registerId;

        return $this;
    }

    /**
     * @param int|null $customerId
     * @return GetUnitSaleRefundsAction
     */
    public function withCustomerId(?int $customerId): GetUnitSaleRefundsAction
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * @param array $createdAtBetween Needs to be in [$from, $to] format with both being Carbon objects
     * @return GetUnitSaleRefundsAction
     * @throws Exception
     */
    public function withCreatedAtBetween(array $createdAtBetween): GetUnitSaleRefundsAction
    {
        if (count($createdAtBetween) !== 2) {
            throw new Exception("The createdAtBetween must has 2 Carbon values.");
        }

        // Make sure that both values are Carbon object
        foreach ($createdAtBetween as $index => $createdAt) {
            if (!$createdAt instanceof Carbon) {
                throw new Exception("The $index element in the createdAtBetween must be an instance of Carbon object.");
            }
        }

        $this->createdAtBetween = $createdAtBetween;

        return $this;
    }

    /**
     * Get the orderBy clause
     * @return array
     */
    private function orderBy(): array
    {
        $column = str_replace('-', '', $this->sort);

        $direction = $this->sort[0] === '-' ? 'desc' : 'asc';

        return [$column, $direction];
    }
}
