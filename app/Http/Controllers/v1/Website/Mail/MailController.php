<?php

namespace App\Http\Controllers\v1\Website\Mail;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Website\Mail\AutoRespondRequest;
use App\Jobs\Email\AutoResponderJob;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Class EmailController
 * @package App\Http\Controllers\v1\Website\Email
 */
class MailController extends RestfulController
{
    /**
     * @var LeadRepositoryInterface
     */
    private $leadRepository;

    /**
     * MailController constructor.
     * @param LeadRepositoryInterface $leadRepository
     */
    public function __construct(LeadRepositoryInterface $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    /**
     * @OA\Put(
     *     path="/api/website/mail/lead/{leadId}/auto-respond",
     *     description="Send auto-respond mail",
     *     tags={"Send auto-respond mail"},
     *     @OA\Parameter(
     *         name="leadId",
     *         in="query",
     *         description="Lead Id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms mail sent",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param int $leadId
     * @return \Dingo\Api\Http\Response|void
     */
    public function autoRespond(int $leadId)
    {
        $request = new AutoRespondRequest(['leadId' => $leadId]);

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $lead = $this->leadRepository->get(['id' => $leadId]);

        try {
            // $job = new AutoResponderJob($lead);
            // $this->dispatch($job->onQueue('mails'));

            return new Response([
                'message' => 'Data has been received and is queued for processing.',
                'result' => true,
            ]);
        } catch (\Exception $e) {
            Log::error("Exception: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->response->errorBadRequest($e->getMessage());
        }
    }
}
