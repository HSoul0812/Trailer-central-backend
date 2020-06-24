<?php

namespace App\Http\Controllers\v1\Dms;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Dms\QuoteRepositoryInterface;
use App\Transformers\Dms\QuoteTransformer;
use App\Http\Requests\Dms\GetQuotesRequest;

/**
 * @author Marcel
 */
class UnitSaleController extends RestfulController
{
    
    protected $quotes;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(QuoteRepositoryInterface $quotes)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->quotes = $quotes;
    }
    
     /**
     * @OA\Get(
     *     path="/api/quotes",
     *     description="Retrieve a list of quotes",     
     *     tags={"Quote"},
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
     *         name="status",
     *         in="query",
     *         description="Status of quote",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of quotes",
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
        $request = new GetQuotesRequest($request->all());
        
        if ($request->validate()) {
            if (empty($request->input('include_group_data'))) {
                return $this->response->paginator($this->quotes->getAll($request->all()), new QuoteTransformer);
            } else {
                return $this->response
                    ->paginator($this->quotes->getAll($request->except(['include_group_data'])), new QuoteTransformer)
                    ->addMeta('groupData', $this->quotes->getAll($request->all()));
            }
        }
        
        return $this->response->errorBadRequest();
    }
    
}
