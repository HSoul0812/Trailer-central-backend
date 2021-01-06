<?php

namespace App\Http\Controllers\v1\Website\Forms;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Website\Forms\FieldMapRepositoryInterface;
use App\Http\Requests\Website\Forms\GetFieldMapRequest;
use App\Http\Requests\Website\Forms\CreateFieldMapRequest;
use App\Http\Requests\Website\Forms\TypesFieldMapRequest;
use App\Transformers\Website\Forms\FieldMapTransformer;

class FieldMapController extends RestfulController
{
    
    protected $fields;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FieldMapRepositoryInterface $fields)
    {
        $this->fields = $fields;
    }
    
    /**
     * @OA\Put(
     *     path="/api/website/forms/field-map",
     *     description="Create a field map",
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Post ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Post title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="post_content",
     *         in="query",
     *         description="Post content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of posts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function create(Request $request) {
        $request = new CreateFieldMapRequest($request->all());
        if ( $request->validate() ) {
            // Create Post
            return $this->response->item($this->fields->create($request->all()), new FieldMapTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/website/forms/field-map",
     *     description="Retrieve a list of field maps by type",
     
     *     tags={"Post"},
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
     *         description="Returns a list of posts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetFieldMapRequest($request->all());
        
        if ( $request->validate() ) {
            $fields = $this->fields->getMap($request->all());
            return $this->response->array([
                'data' => $fields
            ]);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/website/forms/field-map/types",
     *     description="Retrieve a field map types",
     
     *     tags={"Post"},
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
     *         description="Returns a list of posts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function types(Request $request) {
        $request = new TypesFieldMapRequest($request->all());
        if ( $request->validate() ) {
            $fields = $this->fields->getTypes();
            return $this->response->array([
                'data' => $fields
            ]);
        }
        
        return $this->response->errorBadRequest();
    }
}
