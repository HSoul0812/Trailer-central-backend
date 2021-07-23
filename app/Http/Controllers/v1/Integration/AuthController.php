<?php

namespace App\Http\Controllers\v1\Integration;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Http\Requests\Integration\Auth\GetTokenRequest;
use App\Http\Requests\Integration\Auth\CreateTokenRequest;
use App\Http\Requests\Integration\Auth\ShowTokenRequest;
use App\Http\Requests\Integration\Auth\UpdateTokenRequest;
use App\Http\Requests\Integration\Auth\ValidateTokenRequest;
use App\Http\Requests\Integration\Auth\LoginTokenRequest;
use App\Services\Integration\AuthServiceInterface;

class AuthController extends RestfulControllerV2
{
    /**
     * @var AuthServiceInterface
     */
    protected $auth;

    public function __construct(AuthServiceInterface $authService) {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'create', 'update', 'valid']);

        $this->auth = $authService;
    }


    /**
     * @OA\Get(
     *     path="/api/integration/auth/duplicate-entry",
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
     *     path="/api/integration/auth",
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
     *     path="/api/integration/auth/{id}",
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
     * @OA\Put(
     *     path="/api/integration/auth/{id}",
     *     description="Update a text",
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Text ID",
     *         required=true,
     *         @OA\Schema(type="integer")
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
     *     path="/api/integration/auth",
     *     description="Validate an auth token without creating/saving",
     
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
            // Get Common Token
            $accessToken = new CommonToken($request->all());

            // Return Auth
            return $this->response->array($this->auth->validateCustom($accessToken));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Put(
     *     path="/api/integration/auth/login",
     *     description="Initialize login process",
     
     *     tags={"Get"},
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
    public function login(Request $request) {
        // Start Login Token Request
        $request = new LoginTokenRequest($request->all());
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->auth->login($request->token_type, $request->scopes, $request->redirect_uri));
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Put(
     *     path="/api/integration/auth/code",
     *     description="Initialize auth code process",
     
     *     tags={"Get"},
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
    public function code(Request $request) {
        // Start Authorize Token Request
        $request = new AuthorizeTokenRequest($request->all());
        if ($request->validate()) {
            // Return Auth
            return $this->response->array($this->auth->authorize($request->token_type, $request->auth_code, $request->state, $request->scopes, $request->redirect_uri));
        }
        
        return $this->response->errorBadRequest();
    }
}
