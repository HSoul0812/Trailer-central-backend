<?php


namespace App\Http\Controllers\v1\User;


use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\User\CreateDealerLocationMileageFeeRequest;
use App\Http\Requests\User\CreateBulkDealerLocationMileageFeeRequest;
use App\Repositories\User\DealerLocationMileageFeeRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Transformers\User\DealerLocationMileageFeeTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class DealerLocationMileageFeeController extends RestfulControllerV2
{
    /**
     * @var DealerLocationRepositoryInterface
     */
    private $dealerLocationRepository;

    /**
     * @var DealerLocationMileageFeeRepositoryInterface
     */
    private $dealerLocationMileageFeeRepository;

    /**
     * @var DealerLocationMileageFeeTransformer
     */
    private $transformer;

    /**
     * DealerLocationMileageFeeController constructor.
     * @param DealerLocationRepositoryInterface $dealerLocationRepository
     * @param DealerLocationMileageFeeRepositoryInterface $dealerLocationMileageFeeRepository
     */
    public function __construct(
        DealerLocationRepositoryInterface $dealerLocationRepository,
        DealerLocationMileageFeeRepositoryInterface $dealerLocationMileageFeeRepository
    )
    {
        $this->dealerLocationRepository = $dealerLocationRepository;
        $this->dealerLocationMileageFeeRepository = $dealerLocationMileageFeeRepository;
        $this->transformer = new DealerLocationMileageFeeTransformer();
    }

    /**
     * @param int $locationId
     * @param Request $request
     * @return Response
     */
    public function index(int $locationId, Request $request): Response {
        $dealerLocation = $this->dealerLocationRepository->get(['dealer_location_id' => $locationId]);
        return $this->response->collection($dealerLocation->mileageFees, $this->transformer);
    }

    /**
     * @param int $locationId
     * @param Request $request
     * @return Response
     */
    public function create(int $locationId, Request $request): Response {
        $requestData = ['dealer_location_id' => $locationId] + $request->all();
        $request = new CreateDealerLocationMileageFeeRequest($requestData);

        if($request->validate()) {
            $mileageFee = $this->dealerLocationMileageFeeRepository->create(
                $requestData
            );
            return $this->response->item($mileageFee, $this->transformer);
        }
        $this->response->errorBadRequest();
    }

    /**
     * @param int $locationId
     * @param Request $request
     * @return Response
     */
    public function bulkCreate(int $locationId, Request $request): Response {
        $requestData = ['dealer_location_id' => $locationId] + $request->all();
        $request = new CreateBulkDealerLocationMileageFeeRequest($requestData);

        if($request->validate()) {
            $mileageFees = $this->dealerLocationMileageFeeRepository->bulkCreate(
                $requestData
            );
            return $this->response->collection($mileageFees, $this->transformer);
        }
        $this->response->errorBadRequest();
    }

    /**
     * @param int $feeId
     * @param Request $request
     */
    public function delete(int $locationId, int $feeId, Request $request): Response {
        $this->dealerLocationMileageFeeRepository->delete([
            'id' => $feeId
        ]);
        return $this->deletedResponse();
    }
}
