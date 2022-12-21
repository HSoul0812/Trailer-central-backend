<?php

use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Leads\LeadType;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
//    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file' => 'The :attribute must be greater than or equal :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
//    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file' => 'The :attribute must be less than or equal :value kilobytes.',
        'string' => 'The :attribute must be less than or equal :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
        'string' => 'The :attribute may not be greater than :max characters.',
        'array' => 'The :attribute may not have more than :max items.',
    ],
//    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
//    'min' => [
//        'numeric' => 'The :attribute must be at least :min.',
//        'file' => 'The :attribute must be at least :min kilobytes.',
//        'string' => 'The :attribute must be at least :min characters.',
//        'array' => 'The :attribute must have at least :min items.',
//    ],
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
//    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid zone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
//    'url' => 'The :attribute format is invalid.',
    'uuid' => 'The :attribute must be a valid UUID.',
    'phone' => 'The :attribute field contains an invalid phone number.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

//    'custom' => [
//        'attribute-name' => [
//            'rule-name' => 'custom-message',
//        ],
//    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

    'mimes' => 'File extension is not allowed',
    'type_exists' => 'The :attribute does not exist in the DB.',
    'category_exists' => 'The :attribute does not exist in the DB.',
    'brand_exists' => 'The :attribute does not exist in the DB.',
    'manufacturer_exists' => 'The :attribute does not exist in the DB.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'required' => 'The :attribute field is required.',
    'custom' => [
        'phone_number' => [
            'region' => 'The phone number is not the correct format for :region',
        ],
    ],
    'url' => 'The :attribute needs to be a URL',
    'integer' => 'The :attribute needs to be an integer.',
    'array' => 'The :attribute needs to be an array.',
    'price_format' => 'The format for :attribute is incorrect. Starting price needs to be lower than end price. Formats supported are: [10 TO 100], [10.05 TO 100], [10.05 TO 100.05], [10 TO 100.05], [10], [10.05]',
    'lead_type_valid' => 'Lead type status needs to be: '.
                            LeadType::TYPE_BUILD . ', ' .
                            LeadType::TYPE_CALL . ', ' .
                            LeadType::TYPE_GENERAL . ', ' .
                            LeadType::TYPE_CRAIGSLIST . ', ' .
                            LeadType::TYPE_INVENTORY . ', ' .
                            LeadType::TYPE_TEXT . ', ' .
                            LeadType::TYPE_SHOWROOM_MODEL . ', ' .
                            LeadType::TYPE_JOTFORM . ', ' .
                            LeadType::TYPE_RENTALS . ', ' .
                            LeadType::TYPE_FINANCING . ', ' .
                            LeadType::TYPE_SERVICE . ', ' .
                            LeadType::TYPE_TRADE,
    'lead_status_valid' => 'Lead status needs to be: '.
                            Lead::STATUS_HOT . ', ' .
                            Lead::STATUS_COLD . ',' .
                            Lead::STATUS_LOST . ',' .
                            Lead::STATUS_MEDIUM . ',' .
                            Lead::STATUS_NEW_INQUIRY . ',' .
                            Lead::STATUS_UNCONTACTED . ',' .
                            Lead::STATUS_WON . ',' .
                            Lead::STATUS_WON_CLOSED,
    'jotform_enabled' => 'JotForm Disabled or Doesn\'t exist.',
    'interaction_type_valid' => 'Interaction type needs to be: '. implode(',', Interaction::INTERACTION_TYPES),
    'interaction_note_valid' => 'Interaction note is required',
    'sales_person_valid' => 'Sales person ID must exist or be 0',
    'sales_auth_type' => 'SMTP auth needs to be: ' . implode(", ", SalesPerson::SMTP_AUTH),
    'sales_security_type' => 'Security type needs to be: ' . implode(", ", SalesPerson::SECURITY_TYPES),
    'dealer_location_valid' => 'Dealer Location ID must exist or be 0',
    'unique_dealer_location_name' => 'Dealer Location must be unique',
    'website_valid' => 'Website ID must exist or be 0',
    'inventory_valid' => 'Inventory ID must exist',
    'stock_type_valid' => 'The selected :attribute is invalid.',
    'inventory_unique_stock' => 'The selected :attribute already exists on another inventory item.',
    'valid_smtp_email' => 'The selected :attribute doesn\'t have any smtp configuration!',
    'tax_calculator_valid' => 'The selected tax calculator id is invalid',
    'inventory_quotes_not_exist' => 'Can\'t delete inventory linked to quotes',
    'valid_include' => 'The :attribute is not valid',
    'unique_text_blast_campaign_name' => 'You already have a blast campaign with this name',
    'unique_text_campaign_name' => 'You already have a campaign with this name',
    'unique_email_campaign_name' => 'You already have a campaign with this name',
    'unique_email_blast_name' => 'You already have a blast with this name',
    'active_interaction' => 'The number is not active',
    'valid_password' => 'Password should be at least 1 Capital letter, 1 Number and min 8 chars.',
    'allowed_attributes' => 'The :attribute is not allowed',
];
