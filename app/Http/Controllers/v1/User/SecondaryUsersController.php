<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulController;
use App\Repositories\User\DealerUserRepositoryInterface;
use App\Http\Requests\User\GetSecondaryUsersRequest;
use App\Http\Requests\User\CreateSecondaryUserRequest;
use App\Http\Requests\User\UpdateSecondaryUsersRequest;
use App\Transformers\User\DealerUserTransformer;
use Dingo\Api\Http\Request;

class SecondaryUsersController extends RestfulController
{
    /**
     * @var DealerUserRepositoryInterface
     */
    protected $dealerUserRepo;

    public function __construct(DealerUserRepositoryInterface $dealerUserRepo)
    {
        $this->middleware('setDealerIdOnRequest')->only([
            'index', 'create', 'updateBulk'
        ]);

        $this->dealerUserRepo = $dealerUserRepo;
    }

    /**
     * Displays a list of all records in the DB.
     * Paginated or not paginated
     */
    public function index(Request $request)
    {
        $request = new GetSecondaryUsersRequest($request->all());

        if ($request->validate()) {
            return $this->response->collection($this->dealerUserRepo->getByDealer($request->dealer_id), new DealerUserTransformer());
        }

        return $this->response->errorBadRequest();
    }

    public function create(Request $request)
    {
        $request = new CreateSecondaryUserRequest($request->all());

        if ($request->validate()) {
            return $this->response->item($this->dealerUserRepo->create($request->all()), new DealerUserTransformer());
        }

        return $this->response->errorBadRequest();
    }

    public function updateBulk(Request $request)
    {
        $request = new UpdateSecondaryUsersRequest($request->all());

        if ($request->validate()) {
            return $this->response->collection($this->dealerUserRepo->updateBulk($request->all()), new DealerUserTransformer());
        }

        return $this->response->errorBadRequest();
    }
}
