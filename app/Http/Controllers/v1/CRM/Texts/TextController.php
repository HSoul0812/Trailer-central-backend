<?php

namespace App\Http\Controllers\v1\CRM\Texts;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Text\GetTextsRequest;
use App\Http\Requests\CRM\Text\CreateTextRequest;
use App\Http\Requests\CRM\Text\ShowTextRequest;
use App\Http\Requests\CRM\Text\UpdateTextRequest;
use App\Http\Requests\CRM\Text\DeleteTextRequest;
use App\Transformers\CRM\Text\TextTransformer;

class TextController extends RestfulController
{
    protected $texts;

    /**
     * Create a new controller instance.
     *
     * @param Repository $texts
     */
    public function __construct(TextRepositoryInterface $texts)
    {
        $this->texts = $texts;
    }


    /**
     * @OA\Get(
     *     path="/api/crm/{leadId}/texts",
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
        $request = new GetTextsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->texts->getAll($request->all()), new TextTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Put(
     *     path="/api/crm/{leadId}/texts",
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
        $request = new CreateTextRequest($request->all());
        if ( $request->validate() ) {
            // Create Text
            return $this->response->item($this->texts->create($request->all()), new TextTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/crm/{leadId}/texts/{id}",
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
        $request = new ShowTextRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->posts->get(['id' => $id]), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Text(
     *     path="/api/crm/{leadId}/texts/{id}",
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
        $request = new UpdateTextRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->texts->update($request->all()), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Delete(
     *     path="/api/crm/{leadId}/texts/{id}",
     *     description="Delete a text",     
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
        $request = new DeleteTextRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Create Text
            return $this->response->item($this->texts->delete(['id' => $id]), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
}
