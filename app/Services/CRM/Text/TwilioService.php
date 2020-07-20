<?php

namespace App\Services\CRM\Text;

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
            throw new \Exception("The number provided is a landline and cannot receive texts!");
        }

        // Get Twilio Number
        $twilioNumber = Number::getActiveTwilioNumber($from_number, $to_number);

        // Twilio Number Doesn't Exist?
        if (!$twilioNumber) {
            $fromPhone = $this->getNextAvailableNumber();
            if (!$fromPhone) {
                throw new \Exception("Could not find available phone number!");
            }

            // Set Phone as Used
            Number::setPhoneAsUsed($from_number, $fromPhone, $to_number, $fullName);
        } else {
            $fromPhone = $twilioNumber->twilio_number;
        }

        // Initialize Phones
        $phonesTried = [];
        $tries = 0;
        while (true) {
            try {
                // Create/Send Text Message
                $sent = $this->twilio->messages->create(
                    $to_number,
                    array(
                        'from' => $fromPhone,
                        'body' => $textMessage
                    )
                );
            } catch (\Exception $ex) {
                // Exception occurred?!
                if (strpos($ex->getMessage(), 'is not a valid, SMS-capable inbound phone number')) {
                    // Get Next Available Number!
                    $fromPhone = $this->getNextAvailableNumber();
                    if (!$fromPhone) {
                        throw new \Exception("Could not find available phone number!");
                    }

                    // Add Tried Phones to array
                    $phonesTried[] = $fromPhone;
                    if (++$tries == 15) {
                        throw new \Exception("Failed to use 15 different phone numbers, something is seriously wrong here");
                    }

                    // Set Phone as Used!
                    Number::setPhoneAsUsed($from_number, $fromPhone, $to_number, $fullName);
                    continue;
                }
                // Return Other Error
                else {
                    throw new \Exception($ex->getMessage() . ': ' . $ex->getTraceAsString());
                }
            }

            break;
        }

        // TO DO: How to confirm text ACTUALLY sent?! Need to figure out what $this->twilio->messages->create returns.

        // Return Successful Result
        return $sent;
    }

    /**
     * Return next available phone number or false if no available phone numbers
     *
     * @return NumberTwilio || boolean false
     */
    public function getNextAvailableNumber() {
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
