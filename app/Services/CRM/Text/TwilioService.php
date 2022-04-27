<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Text\CreateTwilioMessageException;
use App\Exceptions\CRM\Text\InvalidTwilioInboundNumberException;
use App\Exceptions\CRM\Text\CustomerLandlineNumberException;
use App\Exceptions\CRM\Text\NoTwilioNumberAvailableException;
use App\Exceptions\CRM\Text\TooManyNumbersTriedException;
use App\Exceptions\CRM\Text\SendTwilioTextFailedException;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Repositories\CRM\Text\VerifyRepositoryInterface;
use App\Services\CRM\Text\TwilioServiceInterface;
use App\Models\CRM\Text\NumberTwilio;
use App\Models\CRM\Text\NumberVerify;
use Twilio\Rest\Client;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Illuminate\Support\Facades\Log;

/**
 * Class TwilioService
 *
 * @package App\Services\CRM\Text
 */
class TwilioService implements TwilioServiceInterface
{
    /**
     * @const Code Lengths By Type
     */
    const CODE_LENGTHS = [
        'facebook' => [0, 6]
    ];


    /**
     * @var Twilio Client
     */
    private $twilio;

    /**
     * @var NumberRepositoryInterface
     */
    private $textNumber;

    /**
     * @var VerifyRepositoryInterface
     */
    private $verifyNumber;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var array
     */
    private $from = [];

    /**
     * @var array
     */
    private $to = [];


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
    public function __construct(NumberRepositoryInterface $numberRepo, VerifyRepositoryInterface $verifyRepo) {
        // Get API Keys
        $appId = config('vendor.twilio.sid');
        $authToken = config('vendor.twilio.token');
        $apiKey = config('vendor.twilio.api.key');
        $apiSecret = config('vendor.twilio.api.secret');

        // Initialize Twilio Client
        if(!empty($apiKey) && !empty($apiSecret)) {
            $this->twilio = new Client($apiKey, $apiSecret, $appId);
        } else {
            $this->twilio = new Client($appId, $authToken);
        }

        // Initialize Number Repository
        $this->textNumber = $numberRepo;
        $this->verifyNumber = $verifyRepo;

        // Get From/To Numbers if Exist
        $this->from = config('vendor.twilio.numbers.from');
        $this->to = config('vendor.twilio.numbers.to');

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
     * @param array $mediaUrl
     * @return MessageInstance
     * @throws SendTwilioTextFailedException
     */
    public function send(string $from_number, string $to_number, string $textMessage, string $fullName, array $mediaUrl = []): MessageInstance {
        //try {
            // Send to Demo
            if(!empty($this->from) && !empty($this->from[0])) {
                // Send Demo Number
                return $this->sendDemo($to_number, $textMessage, $mediaUrl);
            }

            // Look Up To Number
            $carrier = $this->twilio->lookups->v1->phoneNumbers($to_number)->fetch(array("type" => array("carrier")))->carrier;
            if (empty($carrier['mobile_country_code'])) {
                //throw new CustomerLandlineNumberException();
            }

            // Send Internal Number
            return $this->sendInternal($from_number, $to_number, $textMessage, $fullName, $mediaUrl);
/*        } catch (\Exception $ex) {
            $this->log->error('Exception occurred trying to send text over Twilio: ' . $ex->getMessage());
            throw new SendTwilioTextFailedException;
        }*/
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
        } catch (\Exception $ex) {
            $this->log->error('Error occurred trying to get Twilio Numbers: ' . $ex->getMessage());
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
            if(!$this->textNumber->existsTwilioNumber($number) &&
               !$this->verifyNumber->exists($number)) {
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

            // Is Env Var Number for Staging/Dev?
            if($number === $this->from) {
                // Do NOT Delete!
                $this->log->info('Can NOT Delete Number ' + $this->from +
                                    '! Number is forced by dev/staging environment!');
                return false;
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
            $this->log->error('Deleted Phone Number ' . $number . ' from DB');
            return true;
        }
        return $success;
    }


    /**
     * Verify Twilio SMS
     *
     * @param string $body
     * @param string $from
     * @param string $to
     * @return null|SmsVerify
     */
    public function verify(string $body, string $from, string $to): ?SmsVerify {
        // Is Verification Number?
        $number = $this->verifyNumber->exists($to);
        if(empty($number->id)) {
            $this->log->error($to . ' is not a valid twilio sms verification number!');
            return null;
        }

        // Return Verification Code
        $this->log->info('Received sms from twilio: ', ['from' => $from, 'to' => $to, 'body' => $body]);
        $code = substr($body, self::CODE_LENGTHS[$number->verify_type][0], self::CODE_LENGTHS[$number->verify_type][1]);

        // Return Sms Verify
        $this->log->info('Received sms verification code: ', ['to' => $to, 'code' => $code]);
        return $this->verifyNumber->createCode($number->twilio_number, $body, $code);
    }

    /**
     * Create And Return Verification Twilio Number
     *
     * @param int $dealerId
     * @param string $dealerNo
     * @param null|string $type
     * @return null|NumberVerify
     */
    public function getVerifyNumber(string $dealerNo, ?string $type = null): ?NumberVerify {
        // Default Type
        if($type === null) {
            $type = NumberVerify::VERIFY_DEFAULT;
        }

        // Get Next Available Number
        if (!empty($this->twilio)) {
            $phoneNumber = current($this->twilio->availablePhoneNumbers("US")->local->read(array('smsEnabled' => true), 1))->phoneNumber;

            try {
                $phone = $this->twilio->incomingPhoneNumbers
                                ->create(["phoneNumber" => $phoneNumber]);

                $this->twilio->incomingPhoneNumbers($phone->sid)
                                ->update(["smsUrl" => config('vendor.twilio.verify')]);
            } catch (\Exception $ex) {
                $this->log->error('Error occurred getting verification twilio number: ' . $ex->getMessage());
                throw new NoTwilioNumberAvailableException();
            }

            // Insert New Verify Number
            return $this->verifyNumber->create($dealerNo, $phoneNumber, $type);
        }

        // Return Null
        return null;
    }


    /**
     * Send Demo Text
     *
     * @param string $toNumber
     * @param string $textMessage
     * @return MessageInstance
     * @throws InvalidTwilioInboundNumberException
     * @throws CreateTwilioMessageException
     */
    private function sendDemo(string $toNumber, string $textMessage, array $mediaUrl = []): MessageInstance {
        // Get To Override
        $toPhone = $toNumber;
        if(!empty($this->to[0])) {
            $toPhone = in_array($toNumber, $this->to) ? $toNumber : $this->to[0];
        }

        $params = [
            'from' => $this->from[0],
            'body' => $textMessage,
        ];

        if (count($mediaUrl) > 0) {
            $params['mediaUrl'] = $mediaUrl;
        }

        // Try Creating Twilio Message
        try {
            // Create/Send Text Message
            $sent = $this->twilio->messages->create($toPhone, $params);
        } catch (\Exception $ex) {
            // Exception occurred?!
            $this->log->error('Error occurred sending demo twilio text: ' . $ex->getMessage());
            if (strpos($ex->getMessage(), 'is not a valid, SMS-capable inbound phone number')) {
                throw new InvalidTwilioInboundNumberException;
            }

            // Throw Create Twilio Message Exception With Exact Error!
            throw new CreateTwilioMessageException($ex->getMessage());
        }

        // Return Successful Result
        $this->log->info('Sent demo text from ' . $this->from[0] . ' to ' . $toPhone);
        return $sent;
    }


    /**
     * Send Internal Text
     *
     * @param string $from_number
     * @param string $to_number
     * @param string $textMessage
     * @param string $fullName
     * @param array $mediaUrl
     * @return MessageInstance
     * @throws CreateTwilioMessageException
     * @throws NoTwilioNumberAvailableException
     * @throws TooManyNumbersTriedException
     */
    private function sendInternal(
        string $from_number,
        string $to_number,
        string $textMessage,
        string $fullName,
        array $mediaUrl = []
    ): MessageInstance {
        // Get Twilio Number
        $fromPhone = $this->getTwilioNumber($from_number, $to_number, $fullName);

        // Initialize Phones
        $this->tries = 0;
        $this->tried = [];
        while(true) {
            try {
                $sent = $this->sendViaTwilio($fromPhone, $to_number, $textMessage, $mediaUrl);
            } catch (InvalidTwilioInboundNumberException $ex) {
                // Get Next Available Number!
                $fromPhone = $this->getNextAvailableNumber();

                // Add Tried Phones to array
                $this->tried[] = $fromPhone;
                $this->log->error('Error occurred trying to pick twilio number to send text: ' . $ex->getMessage());
                if (++$this->tries == $this->maxTries) {
                    $this->log->error('Exceeded ' . $this->maxTries . ' attempts to send twilio text.');
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
     * @param string $fromPhone
     * @param string $toNumber
     * @param string $textMessage
     * @param array $mediaUrl
     * @return MessageInstance
     * @throws CreateTwilioMessageException
     * @throws InvalidTwilioInboundNumberException
     */
    private function sendViaTwilio(string $fromPhone, string $toNumber, string $textMessage, array $mediaUrl = []): MessageInstance {
        $params = [
            'from' => $fromPhone,
            'body' => $textMessage
        ];

        if (count($mediaUrl) > 0) {
            $params['mediaUrl'] = $mediaUrl;
        }

        // Try Creating Twilio Message
        try {
            // Create/Send Text Message
            $sent = $this->twilio->messages->create($toNumber, $params);
        } catch (\Exception $ex) {
            // Exception occurred?!
            $this->log->error('Error occurred sending twilio text: ' . $ex->getMessage());
            if (strpos($ex->getMessage(), 'is not a valid, SMS-capable inbound phone number')) {
                throw new InvalidTwilioInboundNumberException;
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
     * @param string $from_number
     * @param string $to_number
     * @param string $customer_name
     * @return string
     * @throws NoTwilioNumberAvailableException
     */
    private function getTwilioNumber(string $from_number, string $to_number, string $customer_name): string {
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
     * @return null|string
     * @throws NoTwilioNumberAvailableException
     */
    private function getNextAvailableNumber(): ?string {
        // Get Next Available Number
        if (!empty($this->twilio)) {
            $phoneNumber = current($this->twilio->availablePhoneNumbers("US")->local->read(array('smsEnabled' => true), 1))->phoneNumber;

            try {
                $phone = $this->twilio->incomingPhoneNumbers
                                ->create(["phoneNumber" => $phoneNumber]);

                $this->twilio->incomingPhoneNumbers($phone->sid)
                                ->update(["smsUrl" => config('vendor.twilio.reply')]);
            } catch (\Exception $ex) {
                $this->log->error('Error occurred getting twilio number: ' . $ex->getMessage());
                throw new NoTwilioNumberAvailableException();
            }

            // Insert New Twilio Number
            $this->textNumber->createTwilioNumber($phoneNumber);

            // Return Phone Number
            return $phoneNumber;
        }

        // Return Null
        return null;
    }
}
