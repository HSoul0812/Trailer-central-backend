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
    const FIELD_USER = 'username';

    /**
     * Username Field
     */
    const FIELD_PASS = 'password';


    /**
     * Type Authy
     */
    const TYPE_AUTHY = 'authy';

    /**
     * Type SMS
     */
    const TYPE_SMS = 'sms';


    /**
     * @const array<array<string>> TFA Input Methods
     */
    const TFA_FIELDS = [
        'default' => [
            self::FIELD_USER => [
                'label' => 'Username',
                'method' => 'text'
            ],
            self::FIELD_PASS => [
                'label' => 'Password',
                'method' => 'password'
            ]
        ],
        'sms' => [
            self::FIELD_USER => [
                'label' => 'SMS Number',
                'method' => 'autocomplete'
            ],
            self::FIELD_PASS => [
                'label' => 'TFA Number',
                'method' => 'locked'
            ]
        ]
    ];

    /**
     * @const array<string> TFA Input Methods
     */
    const TFA_NOTES = [
        'authy' => '',
        'sms' => 'Enter a phone number or choose an already existing one. ' .
                    'A new phone number will be returned in the second field. ' .
                    'Please set up your Facebook account to use two-factor ' .
                    'authentication using this phone number as an sms number.'
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
        return self::TFA_FIELDS['default'];
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