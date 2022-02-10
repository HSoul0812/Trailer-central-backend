<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Leads\SendInquiryFailedException;
use App\Models\CRM\Leads\LeadType;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\CRM\Text\TextServiceInterface;
use Twilio\Rest\Api\V2010\Account\MessageInstance;

/**
 * Class InquiryTextService
 *
 * @package App\Services\CRM\Leads
 */
class InquiryTextService implements InquiryTextServiceInterface
{
    /**
     * @var App\Services\CRM\Text\TextServiceInterface
     */
    protected $textService;

    /**
     * @var App\Repositories\User\DealerLocationRepositoryInterface
     */
    protected $dealerLocation;

    /**
     * @param TextServiceInterface $textService
     * @param DealerLocationRepositoryInterface $dealerLocation
     */
    public function __construct(
        TextServiceInterface $textService,
        DealerLocationRepositoryInterface $dealerLocation
    ) {
        $this->textService = $textService;
        $this->dealerLocation = $dealerLocation;
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
        $dealerNumber = $this->dealerLocation->findDealerNumber($params['dealer_id'], $params['dealer_location_id']);

        $customerName = $params['customer_name'];
        $customerNumber = $params['phone_number'];

        $messageBody = 'A customer has made an inquiry about model with stock #: ' . $params['inventory_name'] .
            "\nSent From: $customerNumber\nCustomer Name: " . $customerName . "\nUnit link: " . $params['referral'] . "\n\n" . $params['sms_message'];

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
            'lead_types'          => [LeadType::TYPE_TEXT],
            'title'               => $params['inventory_name'],
            'preferred_contact'   => 'phone',
            'comments'            => $params['sms_message']
        ];
    }
}
