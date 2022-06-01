<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Leads\SendInquiryFailedException;
use App\Models\CRM\Leads\LeadType;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
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
     * @var App\Repositories\Inventory\InventoryRepositoryInterface
     */
    protected $inventory;

    /**
     * @param TextServiceInterface $textService
     * @param DealerLocationRepositoryInterface $dealerLocation
     * @param InventoryRepositoryInterface $inventory
     */
    public function __construct(
        TextServiceInterface $textService,
        DealerLocationRepositoryInterface $dealerLocation,
        InventoryRepositoryInterface $inventory
    ){
        $this->textService = $textService;
        $this->dealerLocation = $dealerLocation;
        $this->inventory = $inventory;
    }

    /**
     * Send Text for Lead
     *
     * @param array $params
     * @throws SendInquiryFailedException
     * @throws \InvalidArgumentException if `dealer_id` is not provided
     * @throws \InvalidArgumentException if `dealer_location_id` is not provided
     * @throws \InvalidArgumentException if `customer_name` is not provided
     * @throws \InvalidArgumentException if `phone_number` is not provided
     * @throws \InvalidArgumentException if `referral` is not provided
     * @throws \InvalidArgumentException if `sms_message` is not provided
     * @return MessageInstance
     */
    public function send(array $params): MessageInstance
    {
        collect([
            'dealer_id',
            'dealer_location_id',
            'customer_name',
            'phone_number',
            'referral',
            'sms_message'
        ])->each(function ($key) use ($params) {
            if (!isset($params[$key])) {
                throw new \InvalidArgumentException("Cannot send SMS: `$key` empty");
            }
        });

        $dealerNumber = $this->dealerLocation->findDealerNumber($params['dealer_id'], $params['dealer_location_id']);

        $customerName = $params['customer_name'];
        $customerNumber = $params['phone_number'];

        $messageTitle = 'General Inquiry';
        if (isset($params['inventory_name'])) {
            $messageTitle = 'A customer has made an inquiry about model with stock #: ' . $params['inventory_name'];
        }

        $messageBody =  $messageTitle .
            "\nSent From: $customerNumber\nCustomer Name: " . $customerName . "\nUnit link: " . $params['referral'] . "\n\n" . $params['sms_message'];

        return $this->textService->send($customerNumber, $dealerNumber, $messageBody, $customerName);
    }

    /**
     * Merge default values with request params
     *
     * @param array $params
     * @throws \InvalidArgumentException if `customer_name` is not provided
     * @throws \InvalidArgumentException if `sms_message` is not provided
     * @return array
     */
    public function merge(array $params): array
    {
        collect([
            'customer_name',
            'sms_message'
        ])->each(function ($key) use ($params) {
            if (!isset($params[$key])) {
                throw new \InvalidArgumentException("Cannot send SMS: `$key` empty");
            }
        });

        $name = $params['customer_name'];
        if (strpos($name, ' ') !== FALSE) {
            $namePieces = explode(' ', $name, 2);
            $params['first_name'] = $namePieces[0];
            $params['last_name'] = $namePieces[1];
        } else {
            $params['first_name'] = $name;
            $params['last_name'] = '';
        }

        if (!isset($params['inventory_id'])) {
            $params['inventory'] = [];
        }

        // GetInquiry Stock/Url/Title from the Inventory ID
        if(!empty($params['inventory'][0]) && isset($params['inventory_name']) &&
                $params['inventory_name'] === 'not_set') {
            $inventory = $this->inventory->get(['id' => $params['inventory'][0]]);
            $params['inventory_name'] = $inventory->stock;
        }

        return $params +  [
            'lead_types'          => [LeadType::TYPE_TEXT],
            'title'               => $params['inventory_name'] ?? '',
            'preferred_contact'   => 'phone',
            'comments'            => $params['sms_message']
        ];
    }
}
