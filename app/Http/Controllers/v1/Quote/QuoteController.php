<?php

namespace App\Http\Controllers\v1\Quote;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Quote\QuoteRepositoryInterface;
use App\Transformers\Quote\QuoteTransformer;
use App\Http\Requests\Quote\GetQuotesRequest;

/**
 * @author Marcel
 */
class QuoteController extends RestfulController
{
    
    protected $quotes;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(QuoteRepositoryInterface $quotes)
    {
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
            return $this->response->paginator($this->quotes->getAll($request->all()), new QuoteTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
}
