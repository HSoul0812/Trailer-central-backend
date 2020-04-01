<?php

namespace App\Http\Controllers\v1\Interactions;

use App\Http\Controllers\RestfulController;
use App\Mail\InteractionEmail;
use App\Models\Interactions\Lead;
use App\Repositories\Repository;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InteractionsController extends RestfulController
{
    protected $interactions;

    /**
     * Create a new controller instance.
     *
     * @param Repository $interactions
     */
    public function __construct(Repository $interactions)
    {
        $this->interactions = $interactions;
    }

    public function index(Request $request)
    {
        return response()->json([
            'success'   => true,
            'message'   => "Interactions API",
        ], Response::HTTP_OK);
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
     *                     property="lead_id",
     *                     description="Lead ID.",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="product_id",
     *                     description="Product ID.",
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
     *         description="Lead not found",
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
     *                         "message": "Lead not found",
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
        try {

            $leadId     = $request->input('lead_id');
            $productId  = $request->input('product_id');
            $messageId  = $request->input('message_id');
            $subject    = $request->input('subject');
            $body       = $request->input('body');
            $files      = $request->allFiles();

            $lead = Lead::query()->where('identifier', $leadId)->first();

            $attach = [];

            if (! empty($files) && is_array($files)) {
                foreach ($files as $file) {
                    $attach[] = [
                        'path'  => $file->getPathname(),
                        'as'    => $file->getClientOriginalName(),
                        'mime'  => $file->getMimeType(),
                    ];
                }
            }

            Mail::to('test@trailercentral.com')->send(new InteractionEmail([
                'date'          => Carbon::now()->toDateTimeString(),
                'replyToEmail'  => 'ReplyToEmail@trailercentral.com',
                'replyToName'   => 'ReplyToEmail',
                'subject'       => $subject,
                'body'          => $body,
                'attach'        => $attach
            ]));

            return response()->json([
                'success'   => true,
                'message'   => "Email sent successfully",
            ], Response::HTTP_OK);

        } catch (Throwable $throwable) {
            return response()->json([
                'error'     => true,
                'message'   => $throwable->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
