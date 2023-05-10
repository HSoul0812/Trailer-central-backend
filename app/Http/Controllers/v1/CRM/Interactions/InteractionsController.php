<?php

namespace App\Http\Controllers\v1\CRM\Interactions;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Transformers\CRM\Interactions\InteractionTransformer;
use App\Http\Requests\CRM\Interactions\GetInteractionsRequest;
use App\Http\Requests\CRM\Interactions\CreateInteractionRequest;
use App\Http\Requests\CRM\Interactions\ShowInteractionRequest;
use App\Http\Requests\CRM\Interactions\UpdateInteractionRequest;
use App\Http\Requests\CRM\Interactions\SendEmailRequest;
use App\Services\CRM\Interactions\InteractionServiceInterface;
use Dingo\Api\Http\Request;

class InteractionsController extends RestfulControllerV2
{
    protected $interactions;
    protected $service;
    protected $interactionTransformer;

    /**
     * Create a new controller instance.
     *
     * @param Repository $interactions
     */
    public function __construct(
        InteractionsRepositoryInterface $interactions,
        InteractionServiceInterface $service,
        InteractionTransformer $interactionTransformer
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['sendEmail']);
        $this->middleware('setSalesPersonIdOnRequest')->only(['sendEmail']);
        $this->middleware('setUserIdOnRequest')->only(['sendEmail']);
        $this->interactions = $interactions;
        $this->service = $service;
        $this->interactionTransformer = $interactionTransformer;
    }

    public function index(Request $request) {
        $params = $request->all();
        $request = new GetInteractionsRequest($params);
        
        if ($request->validate()) {
            // Return Result
            return $this->response->paginator($this->interactions->getAll($params), $this->interactionTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    public function create(Request $request) {
        $request = new CreateInteractionRequest($request->all());
        if ( $request->validate() ) {
            // Create Text
            return $this->response->item($this->interactions->create($request->all()), $this->interactionTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    public function show(int $leadId, int $id) {
        $request = new ShowInteractionRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->interactions->get(['id' => $id]), $this->interactionTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    public function update(int $leadId, int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateInteractionRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->interactions->update($request->all()), $this->interactionTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Post(path="/interactions/send-email",
     *     tags={"interactions"},
     *     summary="Send interaction email",
     *     description="",
     *     operationId="sendEmail",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="user_id",
     *                     description="User Authentication ID.",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     required=false,
     *                     property="lead_id",
     *                     description="Lead ID.",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     required=false,
     *                     property="quote_id",
     *                     description="Quote ID.",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="message_id",
     *                     description="Message ID.",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="subject",
     *                     description="Email subject.",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="body",
     *                     description="Email body.",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="filename",
     *                     description="Email attach file.",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     )
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
    public function sendEmail(Request $request)
    {
        $params = $request->all();

        $files = $request->allFiles();
        $request = new SendEmailRequest($params);


        if ( $request->validate()) {
            // Get Results
            $result = $this->service->email($params, $files);
            
            // Send Email Response
            return $this->response->item($result, $this->interactionTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
}
