<?php
namespace App\Services\Ecommerce\CompletedOrder;


use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;

class CompletedOrderService implements CompletedOrderServiceInterface
{

    /** @var CompletedOrderRepositoryInterface */
    private $completedOrderRepository;

    /**
     * CompletedOrderService constructor.
     * @param CompletedOrderRepositoryInterface $completedOrderRepository
     */
    public function __construct(CompletedOrderRepositoryInterface $completedOrderRepository)
    {
        $this->completedOrderRepository = $completedOrderRepository;
    }

    public function create(array $params): CompletedOrder
    {
        return $this->completedOrderRepository->create($params);
    }
}