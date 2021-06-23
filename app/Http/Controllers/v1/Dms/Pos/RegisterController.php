<?php

namespace App\Http\Controllers\v1\Dms\Pos;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Dms\Pos\GetRegistersRequest;
use App\Http\Requests\Dms\Pos\PostOpenRegisterRequest;
use App\Repositories\Dms\Pos\RegisterRepositoryInterface;
use Dingo\Api\Http\Request;

class RegisterController extends RestfulControllerV2
{
    /**
     * @var RegisterRepositoryInterface
     */
    protected $registerRepository;

    public function __construct(RegisterRepositoryInterface $registerRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'open']);
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

    public function open(Request $request)
    {
        $request = new PostOpenRegisterRequest($request->all());

        if ($request->validate() && $this->registerRepository->open($request->all())) {
            return $this->response->array([
                'status' => true
            ]);
        }

        return $this->response->errorBadRequest();
    }
}
