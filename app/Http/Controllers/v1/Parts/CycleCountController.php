<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Parts\CycleCountRepositoryInterface;
use App\Http\Requests\Parts\GetCycleCountsRequest;
use App\Http\Requests\Parts\CreateCycleCountRequest;
use App\Http\Requests\Parts\DeleteCycleCountRequest;
use App\Http\Requests\Parts\UpdateCycleCountRequest;
use App\Transformers\Parts\CycleCountTransformer;

/**
 * @author Marcel
 */
class CycleCountController extends RestfulController
{
    
    protected $cycleCounts;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CycleCountRepositoryInterface $cycleCounts)
    {
        $this->middleware('setDealerIdOnRequest')->only(['create']);
        $this->cycleCounts = $cycleCounts;
    }
    
    /**
     * @OA\Get(
     *     path="/api/parts/cycleCounts",
     *     description="Retrieve a list of cycleCounts",     
     *     tags={"Cycle Counts"},
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
     *         description="Dealer IDs",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Dealer ID array"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="bin_id",
     *         in="query",
     *         description="Bin IDs",
     *         required=false,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description="Bin ID array"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_completed",
     *         in="query",
     *         description="Completed Status",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),  
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of cycle counts",
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
        $request = new GetCycleCountsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->cycleCounts->getAll($request->all()), new CycleCountTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Put(
     *     path="/api/parts/cycle-counts/",
     *     description="Create a cycle count for parts",
     *     tags={"Cycle Counts"},
     *     @OA\Parameter(
     *         name="bin_id",
     *         in="query",
     *         description="Bin ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_completed",
     *         in="query",
     *         description="Status of cycle count complete",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="is_balanced",
     *         in="query",
     *         description="Status of cycle count balanced",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *    @OA\Parameter(
     *         name="parts",
     *         in="query",
     *         description="Parts to cycle count",
     *         required=true,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description=""
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a cycle count created",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function create(Request $request) {
        $request = new CreateCycleCountRequest($request->all());
        
        if ( $request->validate() ) {
            return $this->response->item($this->cycleCounts->create($request->all()), new CycleCountTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Post(
     *     path="/api/parts/cycle-counts/{id}",
     *     description="Update a cycle count for parts",
     *     tags={"Cycle Counts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Cycle Count ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="is_completed",
     *         in="query",
     *         description="Status of cycle count complete",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="is_balanced",
     *         in="query",
     *         description="Status of cycle count balanced",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *    @OA\Parameter(
     *         name="parts",
     *         in="query",
     *         description="Parts to cycle count",
     *         required=true,
     *         @OA\Property(
     *            type="array",
     *            @OA\Items(
     *              type="array",
     *              @OA\Items()
     *            ),
     *            description=""
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a cycle count updated",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function update(int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateCycleCountRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->cycleCounts->update($request->all()), new CycleCountTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Delete(
     *     path="/api/parts/cycle-counts/{id}",
     *     description="Delete a cycle count",     
     *     tags={"Cycle Counts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Cycle Count ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms cycle count was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function destroy(int $id) {
        $request = new DeleteCycleCountRequest(['id' => $id]);
        
        if ( $request->validate() && $this->cycleCounts->delete(['id' => $id])) {
            return $this->response->noContent();
        }
        
        return $this->response->errorBadRequest();
    }
    
}
