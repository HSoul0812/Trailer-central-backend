<?php

namespace App\Http\Controllers\v1\CRM\Email;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use App\Services\CRM\Interactions\EmailBuilderServiceInterface;
/*use App\Http\Requests\CRM\Email\GetBlastsRequest;
use App\Http\Requests\CRM\Email\CreateBlastRequest;
use App\Http\Requests\CRM\Email\ShowBlastRequest;
use App\Http\Requests\CRM\Email\UpdateBlastRequest;
use App\Http\Requests\CRM\Email\DeleteBlastRequest;*/
use App\Http\Requests\CRM\Email\SendBlastRequest;
use App\Transformers\CRM\Email\BlastTransformer;
use Dingo\Api\Http\Request;

class BlastController extends RestfulControllerV2
{
    /**
     * @var BlastRepositoryInterface
     */
    protected $blasts;

    /**
     * @var EmailBuilderServiceInterface
     */
    protected $emailbuilder;

    /**
     * Create a new controller instance.
     *
     * @param BlastRepositoryInterface $blasts
     * @param EmailBuilderServiceInterface $emailbuilder
     */
    public function __construct(
        BlastRepositoryInterface $blasts,
        EmailBuilderServiceInterface $emailbuilder
    )
    {
        $this->middleware('setUserIdOnRequest')->only(['index', 'create', 'update', 'send']);
        $this->blasts = $blasts;
        $this->emailbuilder = $emailbuilder;
    }


    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/emails/blast",
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
        $request = new GetBlastsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->blasts->getAll($request->all()), new BlastTransformer());
        }
        
        return $this->response->errorBadRequest();
    }*/
    
    /**
     * @OA\Put(
     *     path="/api/crm/{userId}/emails/blast",
     *     description="Create a blast",
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
        $request = new CreateBlastRequest($request->all());
        if ( $request->validate() ) {
            // Create Email
            return $this->response->item($this->blasts->create($request->all()), new BlastTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }*/

    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/emails/blast/{id}",
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
    /*public function show(int $id) {
        $request = new ShowBlastRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->blasts->get(['id' => $id]), new BlastTransformer());
        }
        
        return $this->response->errorBadRequest();
    }*/
    
    /**
     * @OA\Email(
     *     path="/api/crm/{userId}/emails/blast/{id}",
     *     description="Update a blast",
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
        $request = new UpdateBlastRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->blasts->update($request->all()), new BlastTransformer());
        }
        
        return $this->response->errorBadRequest();
    }*/

    /**
     * @OA\Delete(
     *     path="/api/crm/{userId}/emails/blast/{id}",
     *     description="Delete a blast",
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
        $request = new DeleteBlastRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Create Email
            return $this->response->item($this->blasts->delete(['id' => $id]), new BlastTransformer());
        }
        
        return $this->response->errorBadRequest();
    }*/

    /**
     * @OA\Send(
     *     path="/api/crm/{userId}/emails/blast/{id}/send",
     *     description="Send Blast Email for All Provided Leads",
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
     *         description="Confirms email blast was sent",
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
        $request = new SendBlastRequest($requestData);
        
        if ( $request->validate()) {
            // Create Email
            return $this->response->item($this->emailbuilder->sendBlast($request->all()), new BlastTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
}
