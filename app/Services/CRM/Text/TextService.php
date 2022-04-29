<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Text\NoDealerSmsNumberAvailableException;
use App\Exceptions\CRM\Text\NoLeadSmsNumberAvailableException;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Leads\Lead;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\File\DTOs\FileDto;
use App\Services\File\FileServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class TextService
 * @package App\Services\CRM\Text
 */
class TextService implements TextServiceInterface
{
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
     * @param TwilioServiceInterface $twilioService
     * @param DealerLocationRepositoryInterface $dealerLocationRepository
     * @param StatusRepositoryInterface $statusRepository
     * @param TextRepositoryInterface $textRepository
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        TwilioServiceInterface $twilioService,
        DealerLocationRepositoryInterface $dealerLocationRepository,
        StatusRepositoryInterface $statusRepository,
        TextRepositoryInterface $textRepository,
        FileServiceInterface $fileService,
        LeadRepositoryInterface $leadRepository
    ) {
        $this->twilioService = $twilioService;
        $this->dealerLocationRepository = $dealerLocationRepository;
        $this->statusRepository = $statusRepository;
        $this->textRepository = $textRepository;
        $this->fileService = $fileService;
        $this->leadRepository = $leadRepository;
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
     */
    public function send(int $leadId, string $textMessage, array $mediaUrl = []): TextLog
    {
        // Get Lead/User
        /** @var Lead $lead */
        $lead = $this->leadRepository->get(['id' => $leadId]);
        $fullName = $lead->newDealerUser()->first()->crmUser->full_name;
        $fileDtos = new Collection();

        // Get To Numbers
        $to_number = $lead->text_phone;
        if(empty($to_number)) {
            throw new NoLeadSmsNumberAvailableException();
        }

        // Get From Number
        $from_number = $this->dealerLocationRepository->findDealerNumber($lead->dealer_id, $lead->preferred_location);
        if(empty($from_number)) {
            throw new NoDealerSmsNumberAvailableException();
        }

        if (!empty($mediaUrl)) {
            $fileDtos = $this->fileService->bulkUpload($mediaUrl, $lead->dealer_id);

            $mediaUrl =  $fileDtos->map(function (FileDto $fileDto) {
                return $fileDto->getUrl();
            })->toArray();
        }

        // Send Text
        $this->twilioService->send($from_number, $to_number, $textMessage, $fullName, $mediaUrl);

        // Save Lead Status
        $this->statusRepository->createOrUpdate([
            'lead_id' => $lead->identifier,
            'status' => Lead::STATUS_MEDIUM,
            'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
        ]);

        $files = $fileDtos->map(function (FileDto $fileDto) {
            return [
                'path' => $fileDto->getPath(),
                'type' => $fileDto->getMimeType(),
            ];
        })->toArray();

        // Log SMS
        return $this->textRepository->create([
            'lead_id'     => $leadId,
            'from_number' => $from_number,
            'to_number'   => $to_number,
            'log_message' => $textMessage,
            'files'       => $files
        ]);
    }
}
