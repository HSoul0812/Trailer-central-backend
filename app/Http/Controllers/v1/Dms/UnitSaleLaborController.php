<?php

namespace App\Http\Controllers\v1\Dms;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Dms\UnitSaleLaborRepositoryInterface;
use App\Transformers\Dms\UnitSaleLaborTechnicianReportTransformer;
use Dingo\Api\Http\Response;
use Dingo\Api\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

/**
 * Class UnitSaleLaborController
 * @package App\Http\Controllers\v1\Dms
 */
class UnitSaleLaborController extends RestfulControllerV2
{
    /**
     * @var UnitSaleLaborRepositoryInterface
     */
    protected $unitSaleLaborRepository;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * UnitSaleLaborController constructor.
     * @param UnitSaleLaborRepositoryInterface $unitSaleLaborRepository
     * @param Manager $fractal
     */
    public function __construct(UnitSaleLaborRepositoryInterface $unitSaleLaborRepository, Manager $fractal)
    {
        $this->unitSaleLaborRepository = $unitSaleLaborRepository;
        $this->fractal = $fractal;

        $this->middleware('setDealerIdOnRequest')->only(['getTechnicians', 'getServiceReport']);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getTechnicians(Request $request): Response
    {
        $technicians = $this->unitSaleLaborRepository->getTechnicians($request->all());

        return $this->response->array(['data' => $technicians]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getServiceReport(Request $request): Response
    {
        $result = $this->unitSaleLaborRepository->serviceReport($request->all());
        $data = new Item($result, new UnitSaleLaborTechnicianReportTransformer(), 'data');

        $response = $this->fractal->createData($data)->toArray();
        return $this->response->array($response);
    }
}
