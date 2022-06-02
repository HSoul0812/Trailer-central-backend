<?php
namespace App\Services\Dms\Bills;

use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Repositories\Dms\Quickbooks\BillRepositoryInterface;

class BillService implements BillServiceInterface
{
    /** @var BillRepositoryInterface */
    private $billRepository;

    /**
     * BillService constructor.
     * @param BillRepositoryInterface $billRepository
     */
    public function __construct(BillRepositoryInterface $billRepository)
    {
        $this->billRepository = $billRepository;
    }

    public function create($params): Bill
    {
        return $this->billRepository->create($params);
    }

    public function update($params): Bill
    {
        return $this->billRepository->update($params);
    }

    public function get(array $params): Bill
    {
        return $this->billRepository->get($params);
    }

    public function getAll(array $params, bool $paginated)
    {
        return $this->billRepository->getAll($params, $paginated);
    }

    public function delete(array $params)
    {
        return $this->billRepository->delete($params);
    }
}