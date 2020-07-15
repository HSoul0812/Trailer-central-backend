<?php

namespace App\Http\Controllers\v1\CRM\Interactions;

use App\Http\Controllers\RestfulController;
use App\Models\CRM\Email\Attachment;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Leads\Lead;
use App\Models\User\User;
use App\Repositories\Repository;
use App\Traits\MailHelper;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;


class InteractionsController extends RestfulController
{
    use MailHelper;

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
    public function sendEmail(Request $request)
    {
        try {
            $user = User::findOrFail($request->input('user_id'));
            $lead = Lead::findOrFail($request->input('lead_id'));
            $emailHistory = EmailHistory::getEmailDraft($user->email, $lead->identifier);
            $attachment = new Attachment();
            $dealer = $user->dealer();
            $leadProduct = $lead->product();
            $leadProductId = $leadProduct->id ?? 0;
            $subject = $request->input('subject');
            $body = $request->input('body');
            $uniqueId = $emailHistory->message_id ?? sprintf('<%s@%s>', $this->generateId(), $this->serverHostname());
            $files = $request->allFiles();
            $attach = [];
            $this->setSalesPersonSmtpConfig($user);
            $this->checkAttachmentsSize($files);
            $customer = $this->getCustomer($user, $lead);

            if (!empty($files) && is_array($files)) {
                foreach ($files as $file) {
                    $attach[] = [
                        'path' => $file->getPathname(),
                        'as' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType(),
                    ];
                }
            }

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
