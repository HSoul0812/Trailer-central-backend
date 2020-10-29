<?php

namespace App\Http\Controllers\v1\Integration;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Models\Integration\Auth\AccessToken;
use App\Http\Requests\Integration\Auth\GetTokenRequest;
use App\Http\Requests\Integration\Auth\CreateTokenRequest;
use App\Http\Requests\Integration\Auth\ShowTokenRequest;
use App\Http\Requests\Integration\Auth\UpdateTokenRequest;
use App\Http\Requests\Integration\Auth\ValidateTokenRequest;
use App\Transformers\Integration\Auth\TokenTransformer;
use App\Services\Integration\AuthServiceInterface;
use App\Services\Integration\Auth\GoogleServiceInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class AuthController extends RestfulControllerV2
{

    /**
     * @var TokenRepository
     */
    protected $tokens;

    /**
     * @var AuthServiceInterface
     */
    protected $auth;

    /**
     * @var GoogleServiceInterface
     */
    protected $google;

    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        TokenRepositoryInterface $tokens,
        AuthServiceInterface $authService,
        GoogleServiceInterface $googleService,
        Manager $fractal
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'create', 'update', 'valid']);

        $this->tokens = $tokens;
        $this->auth = $authService;
        $this->google = $googleService;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
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
        // Get Token Request
        $request = new GetTokenRequest($request->all());
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->auth->index($request->all()));
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
        // Create Auth Request
        $request = new CreateTokenRequest($request->all());
        if ( $request->validate() ) {
            // Return Auth
            return $this->response->array($this->auth->create($request->all()));
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
        // Show Auth Request
        $request = new ShowTokenRequest(['id' => $id]);
        if ( $request->validate() ) {
            // Return Auth
            return $this->response->array($this->auth->show($id));
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
        // Update Auth Request
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateTokenRequest($requestData);
        if ( $request->validate() ) {
            // Return Auth
            return $this->response->array($this->auth->update($request->all()));
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
    public function valid(Request $request) {
        // Validate Auth Request
        $request = new ValidateTokenRequest($request->all());
        if ( $request->validate() ) {
            // Get Access Token
            $accessToken = new AccessToken();
            $accessToken->fill($request->all());

            // Return Auth
            return $this->response->array($this->auth->validate($accessToken));
        }
        
        return $this->response->errorBadRequest();
    }
}
