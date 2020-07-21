<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Text\CustomerLandlineNumberException;
use App\Exceptions\CRM\Text\NoTwilioNumberAvailableException;
use App\Services\CRM\Text\TextServiceInterface;
use App\Models\CRM\Text\Number;
use App\Models\CRM\Text\NumberTwilio;
use Twilio\Rest\Client;

/**
 * Class TwilioService
 * 
 * @package App\Services\CRM\Text
 */
class TwilioService implements TextServiceInterface
{
    /**
     * @var Twilio Client
     */
    private $twilio;

    /**
     * @var int
     * @var int
     * @var array
     */
    private $maxTries = 15;
    private $retries = 0;
    private $tried = [];

    /**
     * TwilioService constructor.
     */
    public function __construct()
    {
        // Initialize Twilio Client
        $this->twilio = new Client(env('TWILIO_ACCOUNT_ID'), env('TWILIO_AUTH_TOKEN'));
    }

    /**
     * Send Text to Twilio
     * 
     * @param string $from_number
     * @param string $to_number
     * @param string $textMessage
     * @param string $fullName
     * @return result of $this->twilio->messages->create || array with error
     */
    public function send($from_number, $to_number, $textMessage, $fullName) {
        // Look Up To Number
        $carrier = $this->twilio->lookups->v1->phoneNumbers($to_number)->fetch(array("type" => array("carrier")))->carrier;
        if (empty($carrier['mobile_country_code'])) {
            throw new CustomerLandlineNumberException();
        }

        // Get Twilio Number
        $fromPhone = $this->getTwilioNumber($from_number, $to_number, $fullName);

        // Initialize Phones
        $this->tries = 0;
        $this->tried = [];
        while(true) {
            try {
                $sent = $this->sendViaTwilio($fromPhone, $to_number, $textMessage);
            } catch (InvalidInboundSmsNumberException $ex) {
                // Get Next Available Number!
                $fromPhone = $this->getNextAvailableNumber();
                if (!$fromPhone) {
                    throw new NoTwilioNumberAvailableException();
                }

                // Add Tried Phones to array
                $this->tried[] = $fromPhone;
                if (++$this->tries == $this->maxTries) {
                    throw new TooManyTwilioNumbersTriedException();
                }

                // Set New Number!
                Number::setPhoneAsUsed($from_number, $fromPhone, $to_number, $fullName);
                continue;
            }

            break;
        }

        // Return Message
        return $sent;
    }

    /**
     * Send Text Via Twilio
     * 
     * @param type $fromPhone
     * @param type $toNumber
     * @param type $textMessage
     * @return type
     * @throws NoTwilioNumberAvailableException
     * @throws TooManyTwilioNumbersTriedException
     * @throws CreateTwilioMessageException
     */
    private function sendViaTwilio($fromPhone, $toNumber, $textMessage) {
        // Try Creating Twilio Message
        try {
            // Create/Send Text Message
            $sent = $this->twilio->messages->create(
                $toNumber,
                array(
                    'from' => $fromPhone,
                    'body' => $textMessage
                )
            );
        } catch (\Exception $ex) {
            // Exception occurred?!
            if (strpos($ex->getMessage(), 'is not a valid, SMS-capable inbound phone number')) {
                throw new InvalidInboundSmsNumberException($ex->getMessage());
            }

            // Throw Create Twilio Message Exception With Exact Error!
            throw new CreateTwilioMessageException($ex->getMessage());
        }

        // TO DO: How to confirm text ACTUALLY sent?! Need to figure out what $this->twilio->messages->create returns.

        // Return Successful Result
        return $sent;
    }

    /**
     * Get Twilio Number
     * 
     * @param type $from_number
     * @param type $to_number
     * @param type $customer_name
     * @return type
     * @throws NoTwilioNumberAvailableException
     */
    private function getTwilioNumber($from_number, $to_number, $customer_name) {
        // Get Active Twilio Number for From/To Numbers
        $twilioNumber = Number::getActiveTwilioNumber($from_number, $to_number);

        // Twilio Number Doesn't Exist?
        if (!$twilioNumber) {
            $fromPhone = $this->getNextAvailableNumber();
            if (!$fromPhone) {
                throw new NoTwilioNumberAvailableException();
            }

            // Set Phone as Used
            Number::setPhoneAsUsed($from_number, $fromPhone, $to_number, $customer_name);
        } else {
            $fromPhone = $twilioNumber->twilio_number;
        }

        // Return From Phone
        return $fromPhone;
    }

    /**
     * Return next available phone number or false if no available phone numbers
     *
     * @return NumberTwilio || boolean false
     */
    private function getNextAvailableNumber() {
        // Get Next Available Number
        if (!empty($this->twilio)) {
            $phoneNumber = current($this->twilio->availablePhoneNumbers("US")->local->read(array('smsEnabled' => true), 1))->phoneNumber;
            
            try {
                $phone = $this->twilio->incomingPhoneNumbers
                                ->create(["phoneNumber" => $phoneNumber]);

                $this->twilio->incomingPhoneNumbers($phone->sid)
                                ->update([
                                        "smsUrl" => "http://crm.trailercentral.com/twilio/reply-twilio-message"
                                    ]
                                );
            } catch (\Exception $ex) {
                return false;
            }

            // Insert New Twilio Number
            NumberTwilio::create(['phone_number' => $phoneNumber]);

            // Return Phone Number
            return $phoneNumber;
        }

        // Return
        return false;
    }
}
