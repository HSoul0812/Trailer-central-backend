<?php

namespace App\Http\Controllers\v1\Integration\Auth;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Integration\Auth\AuthRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\Integration\Auth\GetAuthRequest;
use App\Http\Requests\Integration\Auth\CreateAuthRequest;
use App\Http\Requests\Integration\Auth\ShowAuthRequest;
use App\Http\Requests\Integration\Auth\UpdateAuthRequest;
use App\Transformers\Integration\Auth\AuthTransformer;

class AuthController extends RestfulControllerV2
{
    protected $auth;

    /**
     * Create a new controller instance.
     *
     * @param Repository $auth
     */
    public function __construct(AuthRepositoryInterface $auth)
    {
        $this->auth = $auth;

        $this->middleware('setDealerIdOnRequest')->only(['index', 'create']);
    }


    /**
     * @OA\Get(
     *     path="/api/leads/{leadId}/texts",
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
     *     )
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
        $request = new GetAuthRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->auth->getAll($request->all()), new AuthTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Put(
     *     path="/api/leads/{leadId}/texts",
     *     description="Create a text",
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
        $request = new CreateAuthRequest($request->all());
        if ( $request->validate() ) {
            // Create Text
            return $this->response->item($this->auth->create($request->all()), new AuthTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/leads/{leadId}/texts/{id}",
     *     description="Retrieve a text",
     
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
        // Adjust Results
        $params = ['id' => $type];

        // Show Auth Request 
        $request = new ShowAuthRequest($params);
        
        if ( $request->validate() ) {
            return $this->response->item($this->auth->get($params), new AuthTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Text(
     *     path="/api/leads/{leadId}/texts/{id}",
     *     description="Update a text",
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

        // Update Auth Request
        $request = new UpdateAuthRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->auth->update($request->all()), new AuthTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
}
