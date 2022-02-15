<?php

namespace App\Http\Controllers\v1\Dms\ServiceOrder;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Dms\ServiceOrder\TypeRepositoryInterface;
use App\Transformers\Dms\ServiceOrder\TypeTransformer;
use Dingo\Api\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

/**
 * Class TypesController
 * @package App\Http\Controllers\v1\Dms\ServiceOrder
 */
class TypesController extends RestfulControllerV2
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * TypesController constructor.
     * @param TypeRepositoryInterface $typeRepository
     * @param Manager $fractal
     */
    public function __construct(TypeRepositoryInterface $typeRepository, Manager $fractal)
    {
        $this->typeRepository = $typeRepository;
        $this->fractal = $fractal;
    }

    public function index(Request $request)
    {
        $types = $this->typeRepository->withRequest($request)->getAll([]);

        $data = new Collection($types, new TypeTransformer());
        $data->setPaginator(new IlluminatePaginatorAdapter($this->typeRepository->getPaginator()));

        return $this->response->array($this->fractal->createData($data)->toArray());
    }
}
