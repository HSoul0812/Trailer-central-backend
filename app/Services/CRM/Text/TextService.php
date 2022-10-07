<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Text\NoDealerSmsNumberAvailableException;
use App\Exceptions\CRM\Text\NoLeadSmsNumberAvailableException;
use App\Exceptions\CRM\Text\ReplyInvalidArgumentException;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Text\Number;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\File\DTOs\FileDto;
use App\Services\File\FileServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

/**
 * Class TextService
 * @package App\Services\CRM\Text
 */
class TextService implements TextServiceInterface
{
    private const EXPIRATION_TIME = 120 * 60 * 60;

    private const NUM_MEDIA = 10;

    /**
     * @var TwilioServiceInterface
     */
    private $twilioService;

    /**
     * @var FileServiceInterface
     */
    private $fileService;

    /**
     * @var DealerLocationRepositoryInterface
     */
    private $dealerLocationRepository;

    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;

    /**
     * @var TextRepositoryInterface
     */
    private $textRepository;

    /**
     * @var LeadRepositoryInterface
     */
    private $leadRepository;

    /**
     * @var NumberRepositoryInterface
     */
    private $numberRepository;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @param TwilioServiceInterface $twilioService
     * @param DealerLocationRepositoryInterface $dealerLocationRepository
     * @param StatusRepositoryInterface $statusRepository
     * @param TextRepositoryInterface $textRepository
     * @param FileServiceInterface $fileService
     * @param LeadRepositoryInterface $leadRepository
     * @param NumberRepositoryInterface $numberRepository
     */
    public function __construct(
        TwilioServiceInterface $twilioService,
        DealerLocationRepositoryInterface $dealerLocationRepository,
        StatusRepositoryInterface $statusRepository,
        TextRepositoryInterface $textRepository,
        FileServiceInterface $fileService,
        LeadRepositoryInterface $leadRepository,
        NumberRepositoryInterface $numberRepository
    ) {
        $this->twilioService = $twilioService;
        $this->dealerLocationRepository = $dealerLocationRepository;
        $this->statusRepository = $statusRepository;
        $this->textRepository = $textRepository;
        $this->fileService = $fileService;
        $this->leadRepository = $leadRepository;
        $this->numberRepository = $numberRepository;

        $this->log = Log::channel('texts');
    }

    /**
     * Send Text
     *
     * @param int $leadId
     * @param string $textMessage
     * @param array $mediaUrl
     * @return TextLog
     * @throws NoDealerSmsNumberAvailableException
     * @throws NoLeadSmsNumberAvailableException
     * @throws \Exception
     */
    public function send(int $leadId, string $textMessage, array $mediaUrl = []): TextLog
    {
        /** @var Lead $lead */
        $lead = $this->leadRepository->get(['id' => $leadId]);
        $fullName = $lead->newDealerUser()->first()->crmUser->full_name;
        $fileDtos = new Collection();

        $to_number = $lead->text_phone;
        if(empty($to_number)) {
            throw new NoLeadSmsNumberAvailableException();
        }

        $activeNumber = $this->numberRepository->activeTwilioNumberByCustomerNumber($to_number, $lead->dealer_id);

        if (!empty($activeNumber)) {
            $from_number = $activeNumber->dealer_number;
        } else {
            $from_number = $this->dealerLocationRepository->findDealerNumber($lead->dealer_id, $lead->preferred_location);
        }

        if(empty($from_number)) {
            throw new NoDealerSmsNumberAvailableException();
        }

        if (!empty($mediaUrl)) {
            $fileDtos = $this->fileService->bulkUpload($mediaUrl, $lead->dealer_id);

            $mediaUrl =  $fileDtos->map(function (FileDto $fileDto) {
                return $fileDto->getUrl();
            })->toArray();
        }

        try {
            $this->textRepository->beginTransaction();

            $this->twilioService->send($from_number, $to_number, $textMessage, $fullName, $mediaUrl, $lead->dealer_id);

            if ($this->twilioService->getIsNumberInvalid() && !empty($activeNumber)) {
                $this->numberRepository->delete(['id' => $activeNumber->id]);
            }

            // If there was no status, or it was uncontacted, set to medium, otherwise, don't change.
            if (empty($lead->leadStatus) || $lead->leadStatus->status === Lead::STATUS_UNCONTACTED) {
                $status = Lead::STATUS_MEDIUM;
            } else {
                $status = $lead->leadStatus->status;
            }

            $this->statusRepository->createOrUpdate([
                'lead_id' => $lead->identifier,
                'status' => $status,
                'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
            ]);

            $files = $fileDtos->map(function (FileDto $fileDto) {
                return [
                    'path' => $fileDto->getPath(),
                    'type' => $fileDto->getMimeType(),
                ];
            })->toArray();

            $textLog = $this->textRepository->create([
                'lead_id'     => $leadId,
                'from_number' => $from_number,
                'to_number'   => $to_number,
                'log_message' => $textMessage,
                'files'       => $files
            ]);

            $this->textRepository->commitTransaction();
        } catch (\Exception $e) {
            $this->log->error('Send text error. Message' .  $e->getMessage() . " Params - leadId: $leadId, textMessage: - $textMessage");
            $this->textRepository->rollbackTransaction();

            throw $e;
        }

        return $textLog;
    }

    /**
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function reply(array $params): bool
    {
        $from = $params['From'] ?? null;
        $to = $params['To'] ?? null;
        $body = $params['Body'] ?? null;

        if (empty($from) || empty($to) || empty($body)) {
            $this->log->error('Some param has been missed. Params - ' . json_encode($params));
            throw new ReplyInvalidArgumentException('Some param has been missed. Params - ' . json_encode($params));
        }

        $activeNumber = $this->numberRepository->activeTwilioNumber($to, $from);

        if (!$activeNumber instanceof Number) {
            $this->log->error('The number is not active. Params - ' . json_encode($params));
            throw new ReplyInvalidArgumentException('The number is not active. Params - ' . json_encode($params));
        }

        $sendFromDealer = false;
        $toNumber = $activeNumber->dealer_number;
        $customerName = $activeNumber->customer_name;
        $mediaUrl = [];
        $fileDtos = new Collection();
        $expirationTime = time() + self::EXPIRATION_TIME;

        if ($from !== $activeNumber->customer_number) {
            $sendFromDealer = true;
            $toNumber = $activeNumber->customer_number;
        }

        $textLogs = $this->textRepository->findByFromNumberToNumber($toNumber, $from);

        $leadId = $this->findLeadId($textLogs);

        if (empty($customerName) && !empty($leadId)) {
            /** @var Lead $lead */
            $lead = $this->leadRepository->get(['id' => $leadId]);
            $customerName = $lead->full_name;
        }

        $messageBody = ((!$sendFromDealer) ? "Sent From: " . $from . "\nCustomer Name: $customerName\n\n" : '') . $body;

        for ($i = 0; $i < self::NUM_MEDIA; $i++) {
            if (!isset($params["MediaUrl$i"])) {
                continue;
            }

            $mediaUrl[] = $params["MediaUrl$i"];
        }

        if (!empty($mediaUrl)) {
            $fileDtos = $this->fileService->bulkUpload($mediaUrl, 0);

            $mediaUrl =  $fileDtos->map(function (FileDto $fileDto) {
                return $fileDto->getUrl();
            })->toArray();
        }

        try {
            $this->textRepository->beginTransaction();

            $this->twilioService->sendViaTwilio($to, $toNumber, $messageBody, $mediaUrl);

            if ($this->twilioService->getIsNumberInvalid()) {
                $this->numberRepository->delete(['id' => $activeNumber->id]);
            } else {
                $this->numberRepository->updateExpirationDate($expirationTime, $to, $activeNumber->dealer_number);
            }

            $files = $fileDtos->map(function (FileDto $fileDto) {
                return [
                    'path' => $fileDto->getPath(),
                    'type' => $fileDto->getMimeType(),
                ];
            })->toArray();

            /** @var TextLog $textLog */
            $textLog = $this->textRepository->create([
                'lead_id'     => $leadId,
                'from_number' => $from,
                'to_number'   => $toNumber,
                'log_message' => $messageBody,
                'date_sent'   => date('Y-m-d H:i:s'),
                'files'       => $files,
            ]);

            if(trim($body) === 'STOP') {
                $this->textRepository->stop([
                    'sms_number' => $from,
                    'lead_id'    => $leadId,
                    'text_id'    => $textLog->id
                ]);
            }

            $this->textRepository->commitTransaction();
        } catch (\Exception $e) {
            $this->textRepository->rollbackTransaction();
            $this->log->error('Reply error. Message' .  $e->getMessage() . ' Params - ' . json_encode($params));
            throw $e;
        }

        return true;
    }

    /**
     * @param Collection $textLogs
     * @return int
     */
    private function findLeadId(Collection $textLogs): int
    {
        $result = null;
        $leadIds = [];

        /** @var TextLog $textLog */
        foreach($textLogs as $textLog) {
            if(!empty($textLog->lead->first_name) || !empty($textLog->lead->last_name)) {
                $result = $textLog->lead_id;
            }

            $leadIds[] = $textLog->lead_id;
        }

        if($result === null) {
            $result = reset($leadIds);
        }

        return $result ?? 0;
    }
}
