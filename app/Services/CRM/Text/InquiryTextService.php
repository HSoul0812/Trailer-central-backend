<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Leads\SendInquiryFailedException;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use Illuminate\Support\Facades\Log;
use App\Services\CRM\Text\TwilioService;
use Twilio\Rest\Api\V2010\Account\MessageInstance;

/**
 * Class InquiryTextService
 *
 * @package App\Services\CRM\Leads
 */
class InquiryTextService implements InquiryTextServiceInterface
{
    /**
     * @var App\Services\CRM\Text\TwilioService
     */
    protected $textService;

    /**
     * @var App\Repositories\User\DealerLocationRepositoryInterface
     */
    protected $dealerLocation;

    /**
     * @var App\Repositories\CRM\Text\NumberRepositoryInterface
     */
    protected $numberRepo;

    /**
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;

    /**
     * @param InventoryRepositoryInterface $inventory
     * @param DealerLocationRepositoryInterface $dealerLocation
     */
    public function __construct(
        NumberRepositoryInterface $numberRepo,
        DealerLocationRepositoryInterface $dealerLocation
    ) {
        $this->numberRepo = $numberRepo;
        $this->textService = new TwilioService($numberRepo);
        $this->dealerLocation = $dealerLocation;

        // Initialize Logger
        $this->log = Log::channel('leads');
    }

    /**
     * Send Text for Lead
     *
     * @param array $params
     * @throws SendInquiryFailedException
     * @return MessageInstance
     */
    public function send(array $params): MessageInstance
    {
        $dealerNumber = $this->dealerLocation->findDealerNumber($params['dealer_id'], $params['location_id']);

        $customerName = $params['customer_name'];
        $customerNumber = '+1' . $params['phone_number'];

        $messageBody = 'A customer has made an inquiry about model with stock #: ' . $params['inventory_name'] .
            "\nSent From: $customerNumber\nCustomer Name: " . $customerName . "\nUnit link: " . $params['unit_url'] . "\n\n" . $params['sms_message'];

        return $this->textService->send($customerNumber, $dealerNumber, $messageBody, $customerName);
    }

    /**
     * Merge default values with request params
     *
     * @param array $params
     * @return array
     */
    public function merge(array $params): array
    {
        $name = $params['customer_name'];
        if (strpos($name, ' ') !== FALSE) {
            $namePieces = explode(' ', $name, 2);
            $params['first_name'] = $namePieces[0];
            $params['last_name'] = $namePieces[1];
        } else {
            $params['first_name'] = $name;
            $params['last_name'] = '';
        }

        return $params +  [
            'lead_types'          => ['text'],
            'title'               => $params['inventory_name'],
            'preferred_contact'   => 'phone',
            'comments'            => $params['sms_message']
        ];
    }
}
