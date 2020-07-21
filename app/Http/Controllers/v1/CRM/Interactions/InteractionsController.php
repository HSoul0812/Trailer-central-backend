<?php

namespace App\Http\Controllers\v1\CRM\Interactions;

use App\Http\Controllers\RestfulController;
use App\Repositories\Repository;
use App\Transformers\CRM\Interactions\InteractionTransformer;
use Dingo\Api\Http\Request;


class InteractionsController extends RestfulController
{
    protected $interactions;

    /**
     * Create a new controller instance.
     *
     * @param Repository $interactions
     */
    public function __construct(InteractionsRepositoryInterface $interactions)
    {
        $this->interactions = $interactions;
        $this->transformer = new InteractionTransformer();
    }

    public function index(Request $request)
    {
        return $this->response->array([
            'success' => true,
            'message' => "Interactions API",
        ]);
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
     *                     property="lead_id",
     *                     description="Lead ID.",
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
    public function sendEmail(int $leadId, Request $request)
    {
        $params = $request->all();
        $request = new SendEmailRequest($params);
        
        if ( $request->validate()) {
            // Get Results
            $result = $this->interactions->sendEmail($leadId, $params);

            // Send Text
            return $this->response->item($result, new InteractionTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
}
