<?php

namespace App\Http\Controllers\v1\CRM\Text;

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
        $request = new GetTextsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->texts->getAll($request->all()), new TextTransformer());
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
        $request = new CreateTextRequest($request->all());
        if ( $request->validate() ) {
            // Create Text
            return $this->response->item($this->texts->create($request->all()), new TextTransformer());
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
        $request = new ShowTextRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->posts->get(['id' => $id]), new TextTransformer());
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
        $request = new UpdateTextRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->texts->update($request->all()), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Delete(
     *     path="/api/leads/{leadId}/texts/{id}",
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

    /**
     * @OA\Stop(
     *     path="/api/leads/{leadId}/texts/{id}/stop",
     *     description="Stop sending future texts to this number",
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
    public function Stop(int $id) {
        $request = new StopTextRequest(['id' => $id]);
        
        if ( $request->validate()) {
            // Stop Text
            return $this->response->item($this->texts->stop(['id' => $id]), new TextTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Post(path="/leads/{leadId}/texts/send",
     *     tags={"interactions"},
     *     summary="Send interaction text",
     *     description="",
     *     operationId="sendText",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="user_id",
     *                     description="User Authentication ID.",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="lead_id",
     *                     description="Lead ID.",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="body",
     *                     description="Email body.",
     *                     type="string",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="bool",
     *                         description="The response success"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "success": true,
     *                         "message": "Email sent successfully",
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found | Lead not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="bool",
     *                         description="The response error"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "error": true,
     *                         "message": "User not found",
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Throwable message",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="bool",
     *                         description="The response success"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "error": true,
     *                         "message": "Throwable message",
     *                     }
     *                 )
     *             )
     *         }
     *      ),
     * )
     */
    public function sendText(Request $request)
    {
        try {
            $user = User::findOrFail($request->input('user_id'));
            $lead = Lead::findOrFail($request->input('lead_id'));
            $emailHistory = EmailHistory::getEmailDraft($user->email, $lead->identifier);
            $dealer = $user->dealer();
            $leadProduct = $lead->product();
            $leadProductId = $leadProduct->id ?? 0;
            $subject = $request->input('subject');
            $body = $request->input('body');
            $customer = $this->getCustomer($user, $lead);

            if ( !empty($emailHistory) && !empty($emailHistory->interaction_id)) {
                Interaction::find($emailHistory->interaction_id)
                    ->update(["interaction_notes" => "E-Mail Sent: {$subject}"]);
            } else {
                $emailHistory->interaction_id = Interaction::create(
                    array(
                        "lead_product_id"   => $leadProductId,
                        "tc_lead_id"        => $lead->identifier,
                        "user_id"           => $user->user_id,
                        "interaction_type"  => "EMAIL",
                        "interaction_notes" => "E-Mail Sent: {$subject}",
                        "interaction_time"  => Carbon::now()->toDateTimeString(),
                    )
                );
            }
            $customer['email'] = '5206997905-ceda07@inbox.mailtrap.io';
            Mail::to($customer["email"] ?? "" )->send(
                new InteractionEmail([
                    'date' => Carbon::now()->toDateTimeString(),
                    'replyToEmail' => $user->email ?? "",
                    'replyToName' => "{$user->crmUser->first_name} {$user->crmUser->last_name}",
                    'subject' => $subject,
                    'body' => $body,
                    'attach' => $attach,
                    'id' => $uniqueId
                ])
            );

            $insert = [
                'interaction_id'    => $emailHistory->interaction_id,
                'message_id'        => $uniqueId,
                'lead_id'           => $lead->identifier,
                'to_name'           => $customer['name'],
                'to_email'          => $customer['email'],
                'from_email'        => $user->email,
                'from_name'         => $user->username,
                'subject'           => $request->input('subject'),
                'body'              => $request->input('body'),
                'use_html'          => true,
                'root_message_id'   => 0,
                'parent_message_id'   => 0,
            ];

            $attachment->uploadAttachments($files, $dealer, $uniqueId);
            $emailHistory->createOrUpdateEmailHistory($emailHistory, $insert);

            return $this->response->array([
                'success' => true,
                'message' => "Email sent successfully",
            ]);

        } catch (Throwable $throwable) {
            Log::error($throwable->getMessage());
            return $this->response->array([
                'error' => true,
                'message' => $throwable->getMessage()
            ])->setStatusCode(500);
        }
    }
}
