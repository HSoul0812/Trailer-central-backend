<?php

namespace App\Http\Controllers\v1\CRM\Email;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use App\Http\Requests\CRM\Email\GetTemplatesRequest;
/*use App\Http\Requests\CRM\Email\CreateTemplateRequest;
use App\Http\Requests\CRM\Email\ShowTemplateRequest;
use App\Http\Requests\CRM\Email\UpdateTemplateRequest;
use App\Http\Requests\CRM\Email\DeleteTemplateRequest;*/
use App\Http\Requests\CRM\Email\SendTemplateRequest;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Transformers\CRM\Email\TemplateTransformer;
use Dingo\Api\Http\Request;

class TemplateController extends RestfulControllerV2
{
    /**
     * @var EmailBuilderServiceInterface
     */
    protected $templates;

    /**
     * @var EmailBuilderServiceInterface
     */
    protected $emailbuilder;

    /**
     * Create a new controller instance.
     *
     * @param TemplateRepositoryInterface $templates
     * @param EmailBuilderServiceInterface $emailbuilder
     */
    public function __construct(
        TemplateRepositoryInterface $templates,
        EmailBuilderServiceInterface $emailbuilder
    ) {
        $this->middleware('setUserIdOnRequest')->only(['index', 'create', 'send']);
        $this->middleware('setSalesPersonIdOnRequest')->only(['send']);
        $this->templates = $templates;
        $this->emailbuilder = $emailbuilder;
    }


    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/emails/template",
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
     *     ),
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
    public function index(Request $request) {
        $request = new GetTemplatesRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->templates->getAll($request->all()), new TemplateTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Put(
     *     path="/api/crm/{userId}/emails/template",
     *     description="Create a template",
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
        $request = new CreateTemplateRequest($request->all());
        if ( $request->validate() ) {
            // Create Email
            return $this->response->item($this->templates->create($request->all()), new TemplateTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }*/

    /**
     * @OA\Get(
     *     path="/api/crm/{userId}/emails/template/{id}",
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
    /*public function show(int $id) {
        $request = new ShowTemplateRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->templates->get(['id' => $id]), new TemplateTransformer());
        }
        
        return $this->response->errorBadRequest();
    }*/
    
    /**
     * @OA\Put(
     *     path="/api/crm/{userId}/emails/template/{id}",
     *     description="Update a template",
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
        $request = new UpdateTemplateRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->templates->update($request->all()), new TemplateTransformer());
        }
        
        return $this->response->errorBadRequest();
    }*/

    /**
     * @OA\Delete(
     *     path="/api/crm/{userId}/emails/template/{id}",
     *     description="Delete a template",
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
        $request = new DeleteTemplateRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Create Email
            return $this->response->item($this->templates->delete(['id' => $id]), new TemplateTransformer());
        }
        
        return $this->response->errorBadRequest();
    }*/
    
    /**
     * @OA\Post(
     *     path="/api/crm/{userId}/emails/template/{id}/send",
     *     description="Send Template as Email",
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Email Template ID",
     *         required=true,
     *         @OA\Schema(@OA\Schema(type="integer"))
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns Email Sent",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function send(int $id, Request $request) {
        $request = new SendTemplateRequest($request->all() + ['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->array(
                $this->emailbuilder->sendTemplate($id,
                    $request->subject,
                    $request->to_email,
                    $request->sales_person_id ?? 0,
                    $request->from_email ?? ''
                )
            );
        }
        
        return $this->response->errorBadRequest();
    }
}
