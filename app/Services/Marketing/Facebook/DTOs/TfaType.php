<?php

namespace App\Services\Marketing\Facebook\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class TfaType
 * 
 * @package App\Services\Marketing\Facebook\DTOs
 */
class TfaType
{
    use WithConstructor, WithGetter;

    /**
     * Username Field
     */
    const FIELD_USER = 'tfa_username';

    /**
     * Username Field
     */
    const FIELD_PASS = 'tfa_password';

    /**
     * Code Field
     */
    const FIELD_CODE = 'tfa_code';


    /**
     * Type Authy
     */
    const TYPE_AUTHY = 'authy';

    /**
     * Type SMS
     */
    const TYPE_SMS = 'sms';

    /**
     * Type Code
     */
    const TYPE_CODE = 'code';

    /**
     * Default Type
     */
    const TYPE_DEFAULT = self::TYPE_CODE;


    /**
     * @const array<array<string>> TFA Input Methods
     */
    const TFA_FIELDS = [
        self::TYPE_AUTHY => [
            self::FIELD_USER => [
                'label' => 'Username',
                'method' => 'text'
            ],
            self::FIELD_PASS => [
                'label' => 'Password',
                'method' => 'password'
            ]
        ],
        self::TYPE_SMS => [
            self::FIELD_USER => [
                'label' => 'SMS Number',
                'method' => 'autocomplete'
            ],
            self::FIELD_PASS => [
                'label' => 'TFA Number',
                'method' => 'password'
            ]
        ],
        self::TYPE_CODE => [
            self::FIELD_CODE => [
                'label' => '2FA Code',
                'method' => 'text',
            ]
        ],
    ];

    /**
     * @const array<string> TFA Input Methods
     */
    const TFA_NOTES = [
        self::TYPE_AUTHY => '',
        self::TYPE_SMS => 'Enter a phone number or choose an already existing one. ' .
                    'A new phone number will be returned in the second field. ' .
                    'Please set up your Facebook account to use two-factor ' .
                    'authentication using this phone number as an sms number.',
        self::TYPE_CODE => 'Some Facebook accounts require Two Factor Authentication ' .
                    'to be enabled. If the account you are using requires 2FA, you can ' .
                    'find the 32 character code within the Security Settings of your ' .
                    'Facebook Account.'
    ];


    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Collection<string>
     */
    private $autocomplete;


    /**
     * Get TFA Autocomplete Array
     * 
     * @return array<string>
     */
    public function getAutocomplete(): array {
        // Get Autocomplete
        $autocomplete = [];
        if(!empty($this->autocomplete)) {
            foreach($this->autocomplete as $single) { 
                if($this->code === self::TYPE_SMS) {
                    $autocomplete[] = $single->sms_phone;
                } else {
                    $autocomplete[] = $single;
                }
            }
        }

        // Return Array
        return $autocomplete;
    }

    /**
     * Get TFA Type Fields
     * 
     * @return array
     */
    public function getFields(): array {
        // TFA Fields Exists for Current Type?
        if(isset(self::TFA_FIELDS[$this->code])) {
            return self::TFA_FIELDS[$this->code];
        }

        // Return Default
        return self::TFA_FIELDS[self::TYPE_DEFAULT];
    }

    /**
     * Get TFA Type Note
     * 
     * @return string
     */
    public function getNote(): string {
        // TFA Note Exists for Current Type?
        if(isset(self::TFA_NOTES[$this->code])) {
            return self::TFA_NOTES[$this->code];
        }

        // Return Empty
        return '';
    }
}