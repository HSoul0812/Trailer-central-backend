<?php

namespace App\Http\Controllers\v1\Dms\ServiceOrder;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Dms\ServiceOrder\TechnicianRepositoryInterface;
use App\Transformers\Dms\ServiceOrder\TechnicianTransformer;
use Dingo\Api\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

/**
 * Class TechnicianController
 * @package App\Http\Controllers\v1\Dms\ServiceOrder
 */
class TechnicianController extends RestfulControllerV2
{
    /**
     * @var TechnicianRepositoryInterface
     */
    private $technicianRepository;
    /**
     * @var TechnicianTransformer
     */
    private $transformer;
    /**
     * @var Manager
     */
    private $fractal;

    /**
     * TechnicianController constructor.
     * @param TechnicianRepositoryInterface $serviceItemTechnicians
     * @param TechnicianTransformer $transformer
     * @param Manager $fractal
     */
    public function __construct(TechnicianRepositoryInterface $serviceItemTechnicians, TechnicianTransformer $transformer, Manager $fractal)
    {
        $this->technicianRepository = $serviceItemTechnicians;
        $this->transformer = $transformer;
        $this->fractal = $fractal;

        $this->middleware('setDealerIdFilterOnRequest')->only(['index']);
    }

    public function index(Request $request)
    {
        $technicians = $this->technicianRepository->withRequest($request)->getAll([]);

        $data = new Collection($technicians, $this->transformer);
        $data->setPaginator(new IlluminatePaginatorAdapter($this->technicianRepository->getPaginator()));

        return $this->response->array($this->fractal->createData($data)->toArray());
    }
}
