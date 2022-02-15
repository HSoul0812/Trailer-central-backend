<?php

namespace App\Http\Controllers\v1\CRM\Text;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Text\CampaignRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Text\GetCampaignsRequest;
use App\Http\Requests\CRM\Text\CreateCampaignRequest;
use App\Http\Requests\CRM\Text\ShowCampaignRequest;
use App\Http\Requests\CRM\Text\UpdateCampaignRequest;
use App\Http\Requests\CRM\Text\DeleteCampaignRequest;
use App\Http\Requests\CRM\Text\SentCampaignRequest;
use App\Transformers\CRM\Text\CampaignTransformer;

class CampaignController extends RestfulControllerV2
{
    protected $campaigns;

    /**
     * Create a new controller instance.
     *
     * @param Repository $campaigns
     */
    public function __construct(CampaignRepositoryInterface $campaigns, CampaignTransformer $transformer)
    {
        $this->middleware('setUserIdOnRequest')->only(['index', 'create', 'update']);
        $this->campaigns = $campaigns;
        $this->transformer = $transformer;
    }


    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/texts/campaign",
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
        $request = new GetCampaignsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->campaigns->getAll($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Put(
     *     path="/api/crm/{userId}/texts/campaign",
     *     description="Create a campaign",
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
        $request = new CreateCampaignRequest($request->all());
        if ( $request->validate() ) {
            // Create Text
            return $this->response->item($this->campaigns->create($request->all()), $this->transformer);
        }  
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/texts/campaign/{id}",
     *     description="Retrieve a campaign",
     
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
        $request = new ShowCampaignRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->campaigns->get(['id' => $id]), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Post(
     *     path="/api/crm/{userId}/texts/campaign/{id}",
     *     description="Update a campaign",
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
        $request = new UpdateCampaignRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->campaigns->update($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Delete(
     *     path="/api/crm/{userId}/texts/campaign/{id}",
     *     description="Delete a campaign",
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
        $request = new DeleteCampaignRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Create Text
            return $this->response->item($this->campaigns->delete(['id' => $id]), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Post(
     *     path="/api/crm/{userId}/texts/campaign/{id}/sent",
     *     description="Mark campaign and sent to lead",
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
        $request = new SentCampaignRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Create Text
            return $this->response->item($this->campaigns->sent(['id' => $id]), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
}
