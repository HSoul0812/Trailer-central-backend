<?php

namespace App\Http\Controllers\v1\Interactions;

use App\Http\Controllers\RestfulController;
use App\Mail\InteractionEmail;
use App\Models\Interactions\LeadTC;
use App\Models\User\User;
use App\Repositories\Repository;
use App\Traits\CustomerHelper;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InteractionsController extends RestfulController
{
    use CustomerHelper;

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
    public function sendEmail(Request $request)
    {
        $userId     = $request->input('user_id');
        $leadId     = $request->input('lead_id');
        $messageId  = $request->input('message_id');
        $subject    = $request->input('subject');
        $body       = $request->input('body');
        $files      = $request->allFiles();

        try {

            $user = User::whereUserId($userId)->first();

            if (empty($user)) {
                // TODO: 500 error is returned instead of 404 error, it's incorrect
                // return $this->response->errorNotFound("User not found");
                return response()->json([
                    'error'     => true,
                    'message'   => "User not found"
                ], Response::HTTP_NOT_FOUND);
            }

            $lead = LeadTC::whereIdentifier($leadId)->first();

            if (empty($lead)) {
                // TODO: 500 error is returned instead of 404 error, it's incorrect
                // return $this->response->errorNotFound("Lead not found");
                return response()->json([
                    'error'     => true,
                    'message'   => "Lead with identifier '{$leadId}' was not found in the database"
                ], Response::HTTP_NOT_FOUND);
            }

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

            $customer = $this->getCustomer($user, $lead, $leadId);

            $emailHistory = $lead->emailHistory ?? null;

            $result = Mail::to($customer["email"] ?? "")->send(new InteractionEmail([
                'date'          => Carbon::now()->toDateTimeString(),
                'replyToEmail'  => $user->email ?? "",
                'replyToName'   => "{$user->crmUser->first_name} {$user->crmUser->last_name}",
                'subject'       => $subject,
                'body'          => $body,
                'attach'        => $attach
            ]));

            return response()->json([
                'success'   => true,
                'message'   => "Email sent successfully",
            ], Response::HTTP_OK);

        } catch (Throwable $throwable) {
            Log::error($throwable->getMessage());
            return response()->json([
                'error'     => true,
                'message'   => $throwable->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
