<?php

namespace App\Http\Controllers\v1\Interactions;

use App\Http\Controllers\RestfulController;
use App\Mail\InteractionEmail;
use App\Models\CRM\Email\Attachment;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Leads\Lead;
use App\Models\User\User;
use App\Repositories\Repository;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class InteractionsController extends RestfulController
{
    use CustomerHelper, MailHelper;

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
            'success' => true,
            'message' => "Interactions API",
        ], Response::HTTP_OK);
    }

    /**
     * @param $files - mail attachment(-s)
     * @return bool | string
     */
    public function checkAttachmentsSize($files)
    {
        $totalSize = 0;
        foreach ($files as $file) {
            if ($file['size'] > 2097152) {
                throw new Exception("Single upload size must be less than 2 MB.");
            } else if ($totalSize > 8388608) {
                throw new Exception("Total upload size must be less than 8 MB");
            }
            $totalSize += $file['size'];
        }

        return true;
    }

    public function uploadAttachments($files, $dealer, $uniqueId) {
        $messageDir = str_replace(">", "", str_replace("<", "", $uniqueId));

        if (!empty($files) && is_array($files)) {
            $message = $this->checkAttachmentsSize($files);
            if( false !== $message ) {
                return response()->json([
                    'error' => true,
                    'message' => $message
                ], Response::HTTP_BAD_REQUEST);
            }
            foreach ($files as $file) {
                $path_parts = pathinfo( $file->getPathname() );
                $filePath = 'https://email-trailercentral.s3.amazonaws.com/' . 'crm/'
                    . $dealer->id . "/" . $messageDir
                    . "/attachments/{$path_parts['filename']}." . $path_parts['extension'];
                Storage::disk('s3')->put($filePath, file_get_contents($file));
                Attachment::create(['message_id' => $uniqueId, 'filename' => $filePath, 'original_filename' => time() . $file->getClientOriginalName()]);
            }
        }
    }

    public function createOrUpdateEmailHistory($insert = [], $history) {
        $reportFields = [
            'date_sent',
            'date_delivered',
            'date_bounced',
            'date_complained',
            'date_unsubscribed',
            'date_opened',
            'date_clicked',
            'invalid_email',
            'was_skipped'
        ];

        foreach ($insert as $key => $value) {
            if (in_array($key, $reportFields)) {
                if ($key === 'invalid_email' || $key === 'was_skipped') {
                    if (!empty($value))
                        $insert[$key] = 1;
                } else if (!empty($value)) {
                    if ($value === 1) {
                        $insert[$key] = date("Y-m-d H:i:s");
                    } else {
                        $insert[$key] = $value;
                    }
                }
            }
        }
        if(!!$history) {
            $history->update($insert);
        } else {
            EmailHistory::create($insert);
        }
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

            $this->uploadAttachments($files, $dealer, $uniqueId);
            $this->createOrUpdateEmailHistory($insert, $emailHistory);

            return response()->json([
                'success' => true,
                'message' => "Email sent successfully",
            ], Response::HTTP_OK);

        } catch (Throwable $throwable) {
            Log::error($throwable->getMessage());
            return response()->json([
                'error' => true,
                'message' => $throwable->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
