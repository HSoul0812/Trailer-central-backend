<?php

namespace App\Repositories\CRM\Text;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\User\User;
use App\Models\CRM\Dealer\DealerLocation;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Text\Stop;
use Twilio\Rest\Client;

class TextRepository implements TextRepositoryInterface {
    
    private $twilio;

    private $sortOrders = [
        'date_sent' => [
            'field' => 'date_sent',
            'direction' => 'DESC'
        ],
        '-date_sent' => [
            'field' => 'date_sent',
            'direction' => 'ASC'
        ]
    ];
    
    public function create($params) {
        DB::beginTransaction();

        try {
            // Create Text
            $text = TextLog::create($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $text;
    }

    public function delete($params) {
        $text = TextLog::findOrFail($params['id']);

        DB::transaction(function() use (&$text, $params) {
            $params['deleted'] = '1';

            $text->fill($params)->save();
        });

        return $text;
    }

    public function get($params) {
        return TextLog::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = Template::where('id', '>', 0);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['lead_id'])) {
            $query = $query->where('lead_id', $params['lead_id']);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $text = TextLog::findOrFail($params['id']);

        DB::transaction(function() use (&$text, $params) {
            // Fill Text Details
            $text->fill($params)->save();
        });

        return $text;
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }

    public function stop($params) {
        DB::beginTransaction();

        try {
            // Create Stop
            $stop = Stop::create($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $stop;
    }

    /**
     * Send Text
     * 
     * @param type $params
     * @return type
     */
    public function send($params) {
        // Initialize Twilio Client
        $this->twilio = new Client(env('TWILIO_ACCOUNT_ID'), env('TWILIO_AUTH_TOKEN'));

        // Get User
        $user = User::findOrFail($params['user_id']);
        $fullName = $user->crmUser()->first_name . ' ' . $user->crmUser()->last_name;

        // Find Lead ID
        $lead = Lead::findOrFail($params['lead_id']);
        $dealerId = $lead->location->dealer_id;
        $locationId = $lead->dealer_location_id;

        // Get From/To Numbers
        $phone = $params['phone'];
        $to_number = '+' . ((strlen($phone) === 11) ? $phone : '1' . $phone);
        $from_number = DealerLocation::findDealerNumber($dealerId, $locationId);

        // Send Text to Twilio
        $text = $params['text'];
        $result = $this->sendTwilio($from_number, $to_number, $text, $fullName);

        // Return Error?
        if(is_array($result) && isset($result['error'])) {
            return $result;
        }

        // Save Lead Status
        $lead->leadStatus()->setStatus(Lead::STATUS_MEDIUM);
        $lead->leadStatus()->updateNextContactDate();

        // Log SMS
        return TextLog::create([
            'lead_id'     => $lead_id,
            'from_number' => $from_number,
            'to_number'   => $to_number,
            'text'        => $text
        ]);
    }


    /**
     * Send Text to Twilio
     * 
     * @param string $from_number
     * @param string $to_number
     * @param string $text
     * @param string $fullName
     * @return result of $this->twilio->messages->create || array with error
     */
    private function sendTwilio($from_number, $to_number, $text, $fullName) {
        // Look Up To Number
        $carrier = $this->twilio->lookups->v1->phoneNumbers($to_number)->fetch(array("type" => array("carrier")))->carrier;
        if (empty($carrier['mobile_country_code'])) {
            return [
                'error' => 'Error: The number provided is a landline and cannot receive texts!'
            ];
        }

        // Get Twilio Number
        $twilioNumber = Number::getActiveTwilioNumber($from_number, $to_number);

        // Twilio Number Doesn't Exist?
        if (!$twilioNumber) {
            $fromPhone = $this->getNextAvailableNumber();
            if (!$fromPhone) {
                // Return Error!
                return [
                    'error' => 'An error has happened! Please try again later'
                ];
            }

            // Set Phone as Used
            Number::setPhoneAsUsed($fromPhone, $to_number, $fullName);
        } else {
            $fromPhone = $twilioNumber;
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
                        'body' => $text
                    )
                );
            } catch (\Exception $ex) {
                // Exception occurred?!
                if (strpos($ex->getMessage(), 'is not a valid, SMS-capable inbound phone number')) {
                    // Get Next Available Number!
                    $fromPhone = $phoneRouter->getNextAvailableNumber();
                    if (!$fromPhone) {
                        return [
                            'error' => 'An error has happened! Please try again later'
                        ];
                    }
                    $phonesTried[] = $fromPhone;
                    if (++$tries == 15) {
                        return [
                            'error' => 'An error has happened! Please try again later'
                        ];
                    }

                    // Set Phone as Used!
                    Number::setPhoneAsUsed($fromPhone, $to_number, $fullName);
                    continue;
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
            return NumberTwilio::create(['phone_number' => $phoneNumber]);
        }

        // Return
        return false;
    }
}
