<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Text\CreateTwilioMessageException;
use App\Exceptions\CRM\Text\InvalidTwilioInboundNumberException;
use App\Exceptions\CRM\Text\CustomerLandlineNumberException;
use App\Exceptions\CRM\Text\NoTwilioNumberAvailableException;
use App\Exceptions\CRM\Text\TooManyNumbersTriedException;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Services\CRM\Text\TextServiceInterface;
use App\Models\CRM\Text\NumberTwilio;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

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
     * @var NumberRepositoryInterface
     */
    private $textNumber;

    /**
     * @var Log
     */
    private $log;


    /**
     * @var int
     * @var int
     * @var array
     */
    private $maxTries = 15;
    private $retries = 0;
    private $tried = [];

    /**
     * @const array
     */
    const MAGIC_NUMBERS = ['+15005550000', '+15005550007', '+15005550008', '+15005550001', '+15005550006'];

    /**
     * TwilioService constructor.
     */
    public function __construct(NumberRepositoryInterface $numberRepo)
    {
        // Initialize Twilio Client
        $this->twilio = new Client(config('vendor.twilio.sid'), env('TWILIO_AUTH_TOKEN'));

        // Initialize Number Repository
        $this->textNumber = $numberRepo;

        // Initialize Logger
        $this->log = Log::channel('texts');
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
            //throw new CustomerLandlineNumberException();
        }

        // Send Internal Number
        return $this->sendInternal($from_number, $to_number, $textMessage, $fullName);
    }

    /**
     * Get All Twilio Phone Numbers on Account
     * 
     * @param int $max number of results to return
     * @return array<string>
     */
    public function numbers(int $max = 20): array {
        $list = [];
        try {
            // Get All Incoming Phone Numbers Matching Provided Number
            $numbers = $this->twilio->incomingPhoneNumbers->read([], $max);
            foreach ($numbers as $record) {
                $list[] = $record->phoneNumber;
            }

            // Retrieved Phone Numbers!
            $this->log->info('Found ' . count($list) . ' Phone Numbers from Twilio');
        } catch (Exception $ex) {
            $this->log->error('Failed to get Twilio Numbers');
        }

        // Delete Number From DB
        return $list;
    }

    /**
     * Get Twilio Numbers Missing From DB
     * 
     * @param int $max number of results to return
     * @return array<string>
     */
    public function missing(int $max = 20): array {
        // Get All Numbers
        $list = [];
        $numbers = $this->numbers($max);
        foreach($numbers as $number) {
            if(!$this->textNumber->existsTwilioNumber($number)) {
                $list[] = $number;
            }
        }

        // Return List of Phone Numbers
        $this->log->info('Found ' . count($list) . ' Phone Numbers from Twilio Missing in DB');
        return $list;
    }

    /**
     * Release Twilio Number
     * 
     * @param string $number
     * @return bool | true if successfully deleted from Twilio OR DB; false if failed to delete from both
     */
    public function delete(string $number): bool {
        try {
            // Prepend + to Phone
            if(strpos($number, '+') === false) {
                $number = '+' . $number;
            }

            // Get All Incoming Phone Numbers Matching Provided Number
            $success = true;
            $numbers = $this->twilio->incomingPhoneNumbers
                            ->read(["phoneNumber" => $number], 20);
            foreach ($numbers as $record) {
                $this->log->info('Found Twilio Phone Number ' . $record->phoneNumber . ' to Delete');
                $this->twilio->incomingPhoneNumbers($record->sid)->delete();
            }

            // Delete From Twilio
            $this->log->info('Deleted Phone Number ' . $number . ' from Twilio');
        } catch (Exception $ex) {
            $this->log->error('Phone Number ' . $number . ' does not exist on Twilio, removing from DB!');
            $success = false;
        }

        // Delete Number From DB
        if($this->textNumber->deleteTwilioNumber($number)) {
            return true;
        }
        return $success;
    }


    /**
     * Send Internal Text
     * 
     * @param type $from_number
     * @param type $to_number
     * @param type $textMessage
     * @param type $fullName
     * @return boolean
     * @throws TooManyNumbersTriedException
     */
    private function sendInternal($from_number, $to_number, $textMessage, $fullName) {
        // Get Twilio Number
        $fromPhone = $this->getTwilioNumber($from_number, $to_number, $fullName);

        // Initialize Phones
        $this->tries = 0;
        $this->tried = [];
        while(true) {
            try {
                $sent = $this->sendViaTwilio($fromPhone, $to_number, $textMessage);
            } catch (InvalidTwilioInboundNumberException $ex) {
                // Get Next Available Number!
                $fromPhone = $this->getNextAvailableNumber();

                // Add Tried Phones to array
                $this->tried[] = $fromPhone;
                if (++$this->tries == $this->maxTries) {
                    throw new TooManyNumbersTriedException();
                }

                // Set New Number!
                $this->textNumber->setPhoneAsUsed($from_number, $fromPhone, $to_number, $fullName);
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
                throw new InvalidTwilioInboundNumberException();
            }

            // Throw Create Twilio Message Exception With Exact Error!
            throw new CreateTwilioMessageException($ex->getMessage());
        }

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
        $twilioNumber = $this->textNumber->findActiveTwilioNumber($from_number, $to_number);

        // Twilio Number Doesn't Exist?
        if (!$twilioNumber) {
            $fromPhone = $this->getNextAvailableNumber();

            // Set Phone as Used
            $this->textNumber->setPhoneAsUsed($from_number, $fromPhone, $to_number, $customer_name);
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
                throw new NoTwilioNumberAvailableException();
            }

            // Insert New Twilio Number
            $this->textNumber->createTwilioNumber($phoneNumber);

            // Return Phone Number
            return $phoneNumber;
        }

        // Return
        return false;
    }
}
