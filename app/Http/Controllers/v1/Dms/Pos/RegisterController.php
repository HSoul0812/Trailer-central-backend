<?php

namespace App\Http\Controllers\v1\Dms\Pos;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Dms\Pos\GetRegistersRequest;
use App\Repositories\Dms\Pos\RegisterRepositoryInterface;
use App\Transformers\Parts\ManufacturerTransformer;
use Dingo\Api\Http\Request;

class RegisterController extends RestfulControllerV2
{
    /**
     * @var RegisterRepositoryInterface
     */
    protected $registerRepository;

    public function __construct(RegisterRepositoryInterface $registerRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->registerRepository = $registerRepository;
    }

    /**
     * List/browse all available registers.
     *
     * @return void
     */
    public function index(Request $request)
    {
        $request = new GetRegistersRequest($request->all());

        if ($request->validate()) {
            return $this->response->array([
                'data' => $this->registerRepository->getAllByDealerId($request->dealer_id),
            ]);
        }

        return $this->response->errorBadRequest();
    }
}
