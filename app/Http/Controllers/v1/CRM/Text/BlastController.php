<?php

namespace App\Http\Controllers\v1\CRM\Text;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Text\BlastRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Text\GetBlastsRequest;
use App\Http\Requests\CRM\Text\CreateBlastRequest;
use App\Http\Requests\CRM\Text\ShowBlastRequest;
use App\Http\Requests\CRM\Text\UpdateBlastRequest;
use App\Http\Requests\CRM\Text\DeleteBlastRequest;
use App\Http\Requests\CRM\Text\SentBlastRequest;
use App\Transformers\CRM\Text\BlastTransformer;

class BlastController extends RestfulControllerV2
{
    protected $blasts;

    /**
     * Create a new controller instance.
     *
     * @param Repository $blasts
     */
    public function __construct(BlastRepositoryInterface $blasts)
    {
        $this->middleware('setUserIdOnRequest')->only(['index', 'create', 'update']);
        $this->blasts = $blasts;
    }


    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/texts/blast",
     *     description="Retrieve a list of texts by lead id",
     *     tags={"Text"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order can be: price,-price,relevance,title,-title,length,-length",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of texts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetBlastsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->blasts->getAll($request->all()), new BlastTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Put(
     *     path="/api/crm/{userId}/texts/blast",
     *     description="Create a blast",
     *     tags={"Text"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Text ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Text title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="text_content",
     *         in="query",
     *         description="Text content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of texts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function create(Request $request) {
        $request = new CreateBlastRequest($request->all());
        if ( $request->validate() ) {
            // Create Text
            return $this->response->item($this->blasts->create($request->all()), new BlastTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/texts/blast/{id}",
     *     description="Retrieve a blast",
     
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Post ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a post",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function show(int $id) {
        $request = new ShowBlastRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->blasts->get(['id' => $id]), new BlastTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Put(
     *     path="/api/crm/{userId}/texts/blast/{id}",
     *     description="Update a blast",
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Text ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Text title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="text_content",
     *         in="query",
     *         description="Text content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of texts",
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
        $request = new UpdateBlastRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->blasts->update($request->all()), new BlastTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Delete(
     *     path="/api/crm/{userId}/texts/blast/{id}",
     *     description="Delete a blast",
     *     tags={"Text"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Text ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms text was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function destroy(int $id) {
        $request = new DeleteBlastRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Create Text
            return $this->response->item($this->blasts->delete(['id' => $id]), new BlastTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Post(
     *     path="/api/crm/{userId}/texts/blast/{id}/sent",
     *     description="Mark blast and send to lead",
     *     tags={"Text"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Text ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms text was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function sent(int $id) {
        $request = new SentBlastRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Create Text
            return $this->response->item($this->blasts->sent(['id' => $id]), new BlastTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
}
