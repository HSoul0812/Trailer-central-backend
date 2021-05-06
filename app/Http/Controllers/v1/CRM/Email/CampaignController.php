<?php

namespace App\Http\Controllers\v1\CRM\Email;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
/*use App\Http\Requests\CRM\Email\GetCampaignsRequest;
use App\Http\Requests\CRM\Email\CreateCampaignRequest;
use App\Http\Requests\CRM\Email\ShowCampaignRequest;
use App\Http\Requests\CRM\Email\UpdateCampaignRequest;
use App\Http\Requests\CRM\Email\DeleteCampaignRequest;*/
use App\Http\Requests\CRM\Email\SendCampaignRequest;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Transformers\CRM\Email\CampaignTransformer;
use Dingo\Api\Http\Request;

class CampaignController extends RestfulControllerV2
{
    protected $campaigns;
    protected $emailbuilder;

    /**
     * Create a new controller instance.
     *
     * @param CampaignRepositoryInterface $campaignss
     * @param EmailBuilderServiceInterface $emailbuilder
     */
    public function __construct(
        CampaignRepositoryInterface $campaigns,
        EmailBuilderServiceInterface $emailbuilder
    ) {
        $this->middleware('setUserIdOnRequest')->only(['index', 'create', 'update', 'send']);
        $this->campaigns = $campaigns;
        $this->emailbuilder = $emailbuilder;
    }


    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/emails/campaign",
     *     description="Retrieve a list of emails by lead id",
     *     tags={"Email"},
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
     *         description="Returns a list of emails",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    /*public function index(Request $request) {
        $request = new GetCampaignsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->campaigns->getAll($request->all()), new CampaignTransformer());
        }
        
        return $this->response->errorBadRequest();
    }*/
    
    /**
     * @OA\Put(
     *     path="/api/crm/{userId}/emails/campaign",
     *     description="Create a campaign",
     *     tags={"Email"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Email ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Email title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email_content",
     *         in="query",
     *         description="Email content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of emails",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    /*public function create(Request $request) {
        $request = new CreateCampaignRequest($request->all());
        if ( $request->validate() ) {
            // Create Email
            return $this->response->item($this->campaigns->create($request->all()), new CampaignTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }*/

    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/emails/campaign/{id}",
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
    /*public function show(int $id) {
        $request = new ShowCampaignRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->campaigns->get(['id' => $id]), new CampaignTransformer());
        }
        
        return $this->response->errorBadRequest();
    }*/
    
    /**
     * @OA\Email(
     *     path="/api/crm/{userId}/emails/campaign/{id}",
     *     description="Update a campaign",
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Email ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Email title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email_content",
     *         in="query",
     *         description="Email content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of emails",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    /*public function update(int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateCampaignRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->campaigns->update($request->all()), new CampaignTransformer());
        }
        
        return $this->response->errorBadRequest();
    }*/

    /**
     * @OA\Delete(
     *     path="/api/crm/{userId}/emails/campaign/{id}",
     *     description="Delete a campaign",
     *     tags={"Email"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Email ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms email was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    /*public function destroy(int $id) {
        $request = new DeleteCampaignRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Create Email
            return $this->response->item($this->campaigns->delete(['id' => $id]), new CampaignTransformer());
        }
        
        return $this->response->errorBadRequest();
    }*/

    /**
     * @OA\Send(
     *     path="/api/crm/{userId}/emails/campaign/{id}/sent",
     *     description="Send Campaign Email for All Provided Leads",
     *     tags={"Email"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Email Blast ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="leads",
     *         in="path",
     *         description="Array of Leads to Send Email To",
     *         required=true,
     *         @OA\Schema(type="array")
     *     ),
     *     @OA\Parameter(
     *         name="leads.*",
     *         in="path",
     *         description="Lead ID to Send Blast To",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms email was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function send(int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new SendCampaignRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Send Emails for Campaign
            return $this->response->array(
                $this->emailbuilder->sendCampaign($requestData['id'], $requestData['leads'])
            );
        }
        
        return $this->response->errorBadRequest();
    }
}
