<?php

namespace App\Http\Controllers\v1\Inventory\Manufacturers;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Transformers\Inventory\Manufacturers\BrandTransformer;
use App\Repositories\Inventory\Manufacturers\BrandRepositoryInterface;
use App\Http\Requests\Parts\GetBrandsRequest;

/**
 * Class BrandController
 * @package App\Http\Controllers\v1\Brand
 */
class BrandController extends RestfulControllerV2
{

    /**
     * @var BrandRepositoryInterface
     */
    protected $brandRepository;


    /**
     * Create a new controller instance.
     *
     * @param  BrandRepositoryInterface  $brandRepository
     */
    public function __construct(
        BrandRepositoryInterface $brandRepository
    )
    {
        $this->brandRepository = $brandRepository;
    }

    public function index(Request $request) {
        $request = new GetBrandsRequest($request->all());

        if ( $request->validate() ) {
            return $this->response->paginator($this->brandRepository->getAll($request->all()), new BrandTransformer);
        }

        return $this->response->errorBadRequest();
    }

}
