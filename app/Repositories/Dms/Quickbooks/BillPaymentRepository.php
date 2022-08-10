<?php

namespace App\Repositories\Dms\Quickbooks;

use App\Models\CRM\Dms\Quickbooks\BillPayment;
use App\Repositories\RepositoryAbstract;
use Illuminate\Support\Arr;

/**
 * Class BillPaymentRepository
 *
 * @package App\Repositories\Dms\Quickbooks
 */
class BillPaymentRepository extends RepositoryAbstract implements BillPaymentRepositoryInterface
{
    /**
     * @var BillPayment
     */
    private $model;

    /**
     * @param BillPayment $billPayment
     */
    public function __construct(BillPayment $billPayment)
    {
        $this->model = $billPayment;
    }

    private function getFields(): array
    {
        return [
            'dealer_id',
            'bill_id',
            'doc_num',
            'amount',
            'payment_type',
            'date',
            'account_id',
            'memo',
            'qb_id',
        ];
    }

    /**
     * @param $params
     *
     * @return BillPayment
     */
    public function create($params): BillPayment
    {
        $params = Arr::only($params, $this->getFields());

        return $this->model->create($params);
    }
}
