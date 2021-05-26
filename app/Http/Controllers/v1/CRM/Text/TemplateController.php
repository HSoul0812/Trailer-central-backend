<?php

namespace App\Http\Controllers\v1\CRM\Text;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Text\TemplateRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Text\GetTemplatesRequest;
use App\Http\Requests\CRM\Text\CreateTemplateRequest;
use App\Http\Requests\CRM\Text\ShowTemplateRequest;
use App\Http\Requests\CRM\Text\UpdateTemplateRequest;
use App\Http\Requests\CRM\Text\DeleteTemplateRequest;
use App\Transformers\CRM\Text\TemplateTransformer;

class TemplateController extends RestfulControllerV2
{
    protected $templates;

    /**
     * Create a new controller instance.
     *
     * @param Repository $templates
     */
    public function __construct(TemplateRepositoryInterface $templates)
    {
        $this->middleware('setUserIdOnRequest')->only(['index', 'create']);
        $this->templates = $templates;
    }


    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/texts/template",
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
        $request = new GetTemplatesRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->templates->getAll($request->all()), new TemplateTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Put(
     *     path="/api/crm/{userId}/texts/template",
     *     description="Create a template",
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
        $request = new CreateTemplateRequest($request->all());
        if ( $request->validate() ) {
            // Create Text
            return $this->response->item($this->templates->create($request->all()), new TemplateTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/texts/template/{id}",
     *     description="Retrieve a template",
     
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
        $request = new ShowTemplateRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->templates->get(['id' => $id]), new TemplateTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Put(
     *     path="/api/crm/{userId}/texts/template/{id}",
     *     description="Update a template",
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
        $request = new UpdateTemplateRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->templates->update($request->all()), new TemplateTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Delete(
     *     path="/api/crm/{userId}/texts/template/{id}",
     *     description="Delete a template",
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
        $request = new DeleteTemplateRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Create Text
            return $this->response->item($this->templates->delete(['id' => $id]), new TemplateTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
}
