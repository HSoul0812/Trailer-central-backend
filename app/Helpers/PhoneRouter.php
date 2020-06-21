<?php

namespace App\Helpers;

/**
 * Class to decide which phone number should be used to handle communication
 * between a given dealer and a customer
 *
 * @author Eczek
 */
class PhoneRouter {
    /*
     * E-mail to which the warning in case a dealer has capped
     * all of his phone numbers will be sent
     */ 

    private $warningDestination = 'josh@trailercentral.com';

    /*
     * Determines availability of each of TrailerCentral's phone numbers. All
     * numbers are initially set to available.
     * True => Avaiable
     * False => Unavailable
     */
    private $availablePhones = array();

    private $db = null;
    private $dealerId = null;
    private $phoneNumber = null;
    private $locationNumber = null;
    
    private $twilio;

    const EXPIRATION_TIME = 120; // Expiration time set to 120 hours

    /**
     * Initializes DB.
     * Checks phone numbers status on the DB and sets the current phone number
     * availability.
     *
     * @param String $customerNumber
     */
    public function __construct($dealerId, $locationId, $db, $customerNumber, $dealerNumber='', $twilio = null) {

        $this->db = $db;

        $this->dealerId = $dealerId;

        $this->loadPhoneNumbers();  // Load all twilio numbers from the db

        $this->setLocationNumber($locationId); // We set the location number for this location if any
        
        $this->twilio = $twilio;
        
        // Get all numbers for this dealer across all locations
        $phoneNumbers = $this->getAllDealerNumbers();

        // Get phone number for this dealer location. Don't need to query the DB twice
        foreach($phoneNumbers as $phoneNumber) {
            // Set Correct Location's Phone Number
            if($locationId == $phoneNumber['dealer_location_id']) {
                $this->phoneNumber = $phoneNumber['sms_phone'];
            }
        }
        if(empty($this->phoneNumber)) {
            foreach($phoneNumbers as $phoneNumber) {
                $this->phoneNumber = $phoneNumber['sms_phone'];
                break;
            }
        }

        // Get phone number for this dealer location.
        if(!empty($dealerNumber)) {
            $this->phoneNumber = $dealerNumber;
        }

        // Get all phone numbers the dealer or customer are currently using
        $availabilityQuery = $this->db->prepare("  
            SELECT  dealer_texts.twilio_number
            FROM    dealer_texts
            WHERE   dealer_texts.dealer_number = :dealer_number OR dealer_texts.customer_number = :customer_number
        ");

        foreach($phoneNumbers as $singlePhone) {
             $availabilityQuery->execute(array(
                'dealer_number' => $singlePhone['sms_phone'],
                'customer_number' => $customerNumber
            ));

            $unavailablePhoneNumbers = $availabilityQuery->fetchAll(\PDO::FETCH_ASSOC);

            // Iterate through unavailable phone numbers and update our phone list
            foreach ($unavailablePhoneNumbers as $unavailableNumber) {
                $this->availablePhones[$unavailableNumber['twilio_number']] = false;
            }
        }

    }

    /**
     * Returns the dealer number being used for routing.
     *
     * @return String
     */
    public function getDealerNumber() {
        return $this->phoneNumber;
    }

    /**
     * Returns the next available phone number or false if there are no available phone numbers
     *
     * @return String | boolean
     */
    public function getNextAvailableNumber() {
//        $this->removeAllExpired(); // Do some cleanup and free expired numbers. Need to move to a CRON

//        foreach ($this->availablePhones as $phoneNumber => $available) {
//            if ($available) {
//                return $phoneNumber;
//            }
//        } 
        
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
                        
            $insertTwilioNumber = $this->db->prepare(" 
                INSERT INTO twilio_numbers (phone_number) VALUES (:phone_number)
            ");
            $insertTwilioNumber->execute(['phone_number' => $phoneNumber]);            
            return $phoneNumber;
        }

        return false;
    }

    /**
     * Sets $usedNumber as used on our list and DB
     *
     * @param String $usedNumber
     * @param String $customerNumber
     * @param String $customerName
     */
    public function setPhoneAsUsed($usedNumber, $customerNumber, $customerName) {
        $this->availablePhones[$usedNumber] = false; // Update our list
        // Now update the DB

        $insertPhoneQuery = $this->db->prepare(" 
            INSERT INTO dealer_texts (dealer_number, customer_number, twilio_number, customer_name, expiration_time)
            VALUES (:dealer_number, :customer_number, :twilio_number, :customer_name, :expiration_time)            
        ");

        $insertPhoneQuery->execute(array(
            'dealer_number' => $this->phoneNumber,
            'customer_number' => $customerNumber,
            'customer_name' => $customerName,
            'twilio_number' => $usedNumber,
            'expiration_time' => time() + (self::EXPIRATION_TIME * 60 * 60)
        ));

        /*
         * If there are no more phones left for this dealer we send a
         * warning via e-mail.
         * 
         * Doesn't matter anymore because we are buying numbers on demand
         */
        if ($this->noPhonesLeft()) {
            
        }
    }

    /**
     * Resets expiration time for the dealer number associated to this twilio number
     *
     * @param String $dealerNumber
     * @param String $twilioNumber
     */
    public function resetExpirationDate($dealerNumber, $twilioNumber) {
        $resetExpQuery = $this->db->prepare(" 
            UPDATE  dealer_texts
            SET     dealer_texts.expiration_time = :time
            WHERE   dealer_texts.dealer_number = :dealer_number AND dealer_texts.twilio_number = :twilio_number
        ");

        $resetExpQuery->execute(array(
            'dealer_number' => $dealerNumber,
            'twilio_number' => $twilioNumber,
            'time' => time() + (self::EXPIRATION_TIME * 60 * 60)
        ));
    }

    /**
     * Returns the active twilio number for the dealer/customer interaction
     * or false if there is no conversation on going between this dealer phone
     * and the customer.
     *
     * @param String $customerNumber
     * @return Boolean | String
     */
    public function getActiveTwilioNumber($customerNumber) {
        $interactionQuery = $this->db->prepare(" 
            SELECT  dealer_texts.twilio_number
            FROM    dealer_texts
            WHERE   dealer_texts.customer_number = :customer_number AND dealer_texts.dealer_number = :dealer_number
        ");

        $interactionQuery->execute(array(
            'customer_number' => $customerNumber,
            'dealer_number' => $this->phoneNumber
        ));

        $interaction = $interactionQuery->fetchAll(\PDO::FETCH_ASSOC);

        return (empty($interaction)) ? false : $interaction[0]['twilio_number'];
    }

    /**
     * Takes a twilio number and a masked number and determines wheter this masked number is using the twilio number or not
     *
     * @param String $twilioNumber
     * @param String $maskedNumber
     */
    public function isActiveInteraction($twilioNumber, $maskedNumber) {
        $isActiveQuery = $this->db->prepare(" 
            SELECT  dealer_texts.id
            FROM    dealer_texts
            WHERE   dealer_texts.twilio_number = :twilio_number AND (dealer_texts.dealer_number = :masked_number OR dealer_texts.customer_number = :masked_number)
        ");

        $isActiveQuery->execute(array(
            'masked_number' => $maskedNumber,
            'twilio_number' => $twilioNumber
        ));

        $isActive = $isActiveQuery->fetchAll();
        return !empty($isActive);
    }

    /**
     * Just returns the number for the current location
     *
     * @return String
     */
    public function getLocationNumber() {
        return $this->locationNumber;
    }

    /**
     * Returns a human friendly version of the location number
     *
     * @return type
     */
    public function getFormattedLocationNumber() {
        $tmpLocationNumber = substr($this->locationNumber, 2);
        $locationNumberLength = strlen($tmpLocationNumber);
        $locationNumber = '(';

        for($i = 0; $i < $locationNumberLength; $i++) {
            if( $i === 3 ) {
                $locationNumber .= ')-';
            }
            else if( $i === 6 ) {
                $locationNumber .= '-';
            }

            $locationNumber.= $tmpLocationNumber[$i];
        }

        return $locationNumber;
    }
    /**
     * Frees all numbers from conversation that have already expired. Returns the
     * number of deleted records.
     *
     * @return Integer
     */
    private function removeAllExpired() {
        /* Get all expiration times for the numbers assigned to this dealer number.
         * TODO: I could get all numbers to be deleted from this query. Hmm...
         * Maybe I should use that approach?
         */
        $expirationQuery = $this->db->prepare(" 
            SELECT  dealer_number, twilio_number, expiration_time
            FROM    dealer_texts
            WHERE   dealer_texts.dealer_number = :dealer_number
        ");

        $expirationQuery->execute(array(
            'dealer_number' => $this->phoneNumber
        ));

        $expirationTimes = $expirationQuery->fetchAll(\PDO::FETCH_ASSOC);
        $currentTime = time();
        $deletedRecords = 0;

        foreach ($expirationTimes as $expiration) {
            if ((int) $expiration['expiration_time'] <= $currentTime) {
                if ($this->freeNumber($expiration['dealer_number'], $expiration['twilio_number'])) {
                    $deletedRecords++;
                }
            }
        }

        return $deletedRecords;
    }

    /**
     * Checks whether the current active dealer phone number does not have
     * phone numbers left
     *
     * @return boolean
     */
    private function noPhonesLeft() {
        foreach ($this->availablePhones as $phoneStatus) {
            if ($phoneStatus) {
                return false;
            }
        }
        return true;
    }

    /**
     * Frees the twilio number that is linked to the provided dealer number
     *
     * @param String $dealerNumber
     * @param String $twilioNumber
     * @return boolean
     */
    private function freeNumber($dealerNumber, $twilioNumber) {
        $deleteQuery = $this->db->prepare("  
            DELETE 
            FROM    dealer_texts
            WHERE   dealer_texts.dealer_number = :dealer_number AND dealer_texts.twilio_number = :twilio_number    
        ");

        try {
            $deleteQuery->execute(array(
                'dealer_number' => $dealerNumber,
                'twilio_number' => $twilioNumber
            ));
        } catch (Exception $ex) {
            // just return false if it fails
            return false;
        }

        return true;
    }

    /**
     * Sends an e-mail to the administrator signaling that a dealer has run out of numbers to use.
     *
     * @param String $message
     * @return Boolean
     */
    private function sendWarning($message) {
        $headers = 'From: TrailerCentral<warnings@trailercentral.com>' . "\r\n" .
                'Reply-To: warnings@trailercentral.com' . "\r\n" .
                "X-Mailer: PHP/" . phpversion();
        return mail($this->warningDestination, 'WARNING: Dealer has no Twilio Numbers Left', $message, $headers);
    }

    /**
     * Returns all dealer phone numbers for all locations
     *
     * @return ['sms_phone'] => String
     */
    private function getAllDealerNumbers() {
        // Get phone numbers for all dealer locations
        $allPhonesQuery = $this->db->prepare("     
            SELECT  dealer_location.sms_phone, dealer_location.dealer_location_id
            FROM    dealer_location
            WHERE   dealer_location.dealer_id = :dealer_id
        ");

        $allPhonesQuery->execute(array(
            'dealer_id' => $this->dealerId
        ));

        return $allPhonesQuery->fetchAll(\PDO::FETCH_ASSOC);
    }


    /**
     * Returns the phone number
     *
     * @return String
     */
    private function getLocationDealerNumber($locationId) {
        $phoneQuery = $this->db->prepare("
            SELECT  dealer_location.sms_phone
            FROM    dealer_location
            WHERE   dealer_location.dealer_location_id = :location_id AND dealer_location.sms_phone IS NOT NULL
        ");

        $phoneQuery->execute(array(
            'location_id' => $locationId
        ));

        $phoneNumber = $phoneQuery->fetchAll(\PDO::FETCH_ASSOC);
        return $phoneNumber[0]['sms_phone'];
    }

    /**
     * Sets the location number for this dealer
     */
    private function setLocationNumber($locationId) {
        $phoneQuery = $this->db->prepare(" 
            SELECT  dealer_location.permanent_phone
            FROM    dealer_location
            WHERE   dealer_location.dealer_location_id = :location_id
        ");

        $phoneQuery->execute(array(
            'location_id' => $locationId
        ));

        $permanentPhone = $phoneQuery->fetchAll(\PDO::FETCH_ASSOC);
        
        if (empty($permanentPhone)) {
            return;
        }
        
        $permanentPhone = (bool)$permanentPhone[0]['permanent_phone'];

        if( $permanentPhone ) {
            end($this->availablePhones);
            $this->locationNumber = key($this->availablePhones);
            $this->availablePhones[$this->locationNumber] = false; // Set as used
            reset($this->availablePhones);
        }
    }

    /**
     * Loads our available twilio numbers
     */
    private function loadPhoneNumbers() {
        $loadPhonesQuery = $this->db->prepare(" 
            SELECT twilio_numbers.phone_number
            FROM   twilio_numbers
        ");

        $loadPhonesQuery->execute();

        $phones = $loadPhonesQuery->fetchAll(\PDO::FETCH_ASSOC);

        foreach($phones as $singlePhone) {
            $this->availablePhones[$singlePhone['phone_number']] = true;
        }
    }

    /**
     * Log SMS
     */
    public function logSMS($from, $to, $message, $lead_id, $date='') {
        $date_sent = empty($date) ? date('Y-m-d H:i:s') : $date;

        // Initialize Query
        $query = $this->db->prepare("INSERT INTO `dealer_texts_log` (`from_number`, to_number, log_message, lead_id, date_sent)
                                        VALUES(:from, :to, :message, :leadId, :dateSent)");
        $query->execute(array(
            'from'     => $from,
            'to'       => $to,
            'message'  => $message,
            'leadId'   => $lead_id,
            'dateSent' => $date_sent
        ));

        // Return Insert ID
        $id = $this->db->lastInsertId();
        if(!empty($id)) {
            return $id;
        }
        return 0;
    }


    /**
     * Get SMS From DB
     * 
     * @param type $textId
     * @param type $db
     * @param type $dealerId
     * @return type
     */
    public static function getSMS($textId, $db, $dealerId) {
        // Get Text Log
        $phoneQuery = $db->prepare(" 
            SELECT  *
            FROM    dealer_texts_log
            WHERE   id = :textId
        ");
        $phoneQuery->execute(array(
            'textId' => $textId
        ));
        $text = $phoneQuery->fetchAll(\PDO::FETCH_ASSOC)[0];

        // Get All Dealer Numbers
        $allPhonesQuery = $db->prepare("     
            SELECT  dealer_location.phone, dealer_location.sms_phone
            FROM    dealer_location
            WHERE   dealer_location.dealer_id = :dealer_id
        ");
        $allPhonesQuery->execute(array(
            'dealer_id' => $dealerId
        ));
        $dealerNumbers = $allPhonesQuery->fetchAll(\PDO::FETCH_ASSOC);

        $dealerNumber = '';
        $customerNumber = '';

        foreach($dealerNumbers as $dealerNumber) {
            if ($dealerNumber['phone'] == $text['from_number'] || strpos($text['from_number'], $dealerNumber['sms_phone']) !== false ) {
                $dealerNumber = $text['from_number'];
                $customerNumber = $text['to_number'];
                break;
            } else if ($dealerNumber['phone'] == $text['to_number'] || strpos($text['to_number'], $dealerNumber['sms_phone']) !== false) {
                $dealerNumber = $text['to_number'];
                $customerNumber = $text['from_number'];
                break;
            }
        }

        return [
            'text' => $text,
            'dealerNumber' => $dealerNumber,
            'customerNumber' => $customerNumber
        ];
    }
}