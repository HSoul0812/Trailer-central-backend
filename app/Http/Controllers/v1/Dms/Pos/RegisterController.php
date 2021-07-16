<?php

namespace App\Http\Controllers\v1\Dms\Pos;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Dms\Pos\GetRegistersRequest;
use App\Http\Requests\Dms\Pos\PostOpenRegisterRequest;
use App\Repositories\Dms\Pos\RegisterRepositoryInterface;
use App\Services\Dms\Pos\RegisterServiceInterface;
use Dingo\Api\Http\Request;

class RegisterController extends RestfulControllerV2
{
    /**
     * @var RegisterRepositoryInterface
     */
    protected $registerRepository;

    /**
     * @var RegisterServiceInterface
     */
    private $registerService;

    public function __construct(
        RegisterRepositoryInterface $registerRepository,
        RegisterServiceInterface $registerService
    )
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'create']);
        $this->registerRepository = $registerRepository;
        $this->registerService = $registerService;
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

    public function create(Request $request)
    {
        $request = new PostOpenRegisterRequest($request->all());

        if (!$request->validate() || !($registerMessage = $this->registerService->open($request->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->response->array([
            'status' => true,
            'message' => $registerMessage
        ]);
    }
}
