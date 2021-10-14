<?php
namespace App\Services\Ecommerce\CompletedOrder;


use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Parts\Textrail\PartRepository;
use App\Transformers\Ecommerce\CompletedOrderTransformer;
use League\Fractal\Resource\Collection;

class CompletedOrderService implements CompletedOrderServiceInterface
{

    /** @var CompletedOrderRepositoryInterface */
    private $completedOrderRepository;

    /** @var PartRepository */
    private $textRailPartRepoitory;

    /**
     * CompletedOrderService constructor.
     * @param CompletedOrderRepositoryInterface $completedOrderRepository
     * @param PartRepository $textRailPartRepoitory
     */
    public function __construct(CompletedOrderRepositoryInterface $completedOrderRepository, PartRepository $textRailPartRepoitory)
    {
        $this->completedOrderRepository = $completedOrderRepository;
        $this->textRailPartRepoitory = $textRailPartRepoitory;
    }

    public function create(array $params): CompletedOrder
    {
        return $this->completedOrderRepository->create($params);
    }
}