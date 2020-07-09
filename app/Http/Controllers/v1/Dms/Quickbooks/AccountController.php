<?php

namespace App\Http\Controllers\v1\Dms\Quickbooks;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Dms\Quickbooks\AccountRepositoryInterface;
use App\Transformers\Dms\Quickbooks\AccountTransformer;
use App\Http\Requests\Dms\Quickbooks\CreateAccountRequest;
use App\Http\Requests\Dms\Quickbooks\GetAccountsRequest;

/**
 * @author Marcel
 */
class AccountController extends RestfulController
{
    
    protected $accounts;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AccountRepositoryInterface $accounts)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'create']);
        $this->accounts = $accounts;
    }
    
    /**
     * @OA\Get(
     *     path="/api/dms/quickbooks/accounts",
     *     description="Retrieve a list of accounts",     
     *     tags={"Account"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Search term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of accounts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) 
    {
        $request = new GetAccountsRequest($request->all());
        
        if ($request->validate()) {
          return $this->response->paginator($this->accounts->getAll($request->all()), new AccountTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Put(
     *     path="/api/dms/quickbooks/accounts/",
     *     description="Create an account",
     *     tags={"Accounts"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Name of account",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Account Type",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sub_type",
     *         in="query",
     *         description="Account Sub Type",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns an account created",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function create(Request $request) {
      $request = new CreateAccountRequest($request->all());
      
      if ( $request->validate() ) {
          return $this->response->item($this->accounts->create($request->all()), new AccountTransformer());
      }  
      
      return $this->response->errorBadRequest();
  }
    
}
