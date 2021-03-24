<?php

namespace App\Models\Website\Forms;

use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Leads\LeadType;

class FieldMap extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'website_form_field_map';

    // Define Mapping Types
    const MAP_TYPES = [
        'lead' => 'Lead',
        'special_name' => 'Full Name',
        'special_phone' => 'Phone Number',
        'special_address' => 'Address',
        'trade' => 'Trade',
        'lead_type' => 'Lead Type',
        'fandi' => 'F&I'
    ];

    // Define Mapping Fields
    const MAP_FIELDS = [
        'lead' => [
            'referral' => 'Referrer',
            'title' => 'Title',
            'lead_type' => 'Lead Type',
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'special_name' => 'Map to Full Name Type',
            'preferred_contact' => 'Preferred Contact (email/phone)',
            'preferred_location' => 'Preferred Location',
            'preferred_salesperson' => 'Directly Choose Sales Person',
            'special_phone' => 'Phone Number',
            'email_address' => 'Email Address',
            'special_address' => 'Map to Address Type',
            'comments' => 'Comments'
        ],
        'special_name' => [
            'first' => 'First Name',
            'last' => 'Last Name',
            'prefix' => 'Prefix (Prepend to First Name)',
            'middle' => 'Middle Name (Append to First Name)',
            'suffix' => 'Suffix (Append to Last Name)'
        ],
        'special_phone' => [
            'phone_number' => 'Main Phone',
            'area' => 'Area Code',
            'full' => 'Full Phone'
        ],
        'special_address' => [
            'address' => 'Address',
            'address2' => 'Address Line 2 (Appends to Address)',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'Zip Code',
        ],
        'trade' => [
            'type' => 'Type',
            'make' => 'Make',
            'model' => 'Model',
            'year' => 'Year',
            'price' => 'Price',
            'length' => 'Length',
            'width' => 'Width',
            'notes' => 'Notes',
            'photos' => 'Photos'
        ],
        'lead_type' => [
            LeadType::TYPE_FINANCING => 'Financing',
            LeadType::TYPE_BUILD => 'Build a Trailer',
            LeadType::TYPE_RENTALS => 'Rentals',
            LeadType::TYPE_TRADE => 'Trade',
            LeadType::TYPE_SERVICE => 'Service',
            LeadType::TYPE_NONLEAD => 'Non-Lead'
        ]
    ];

    // Define Mapping Tables
    const MAP_TABLES = [
        'lead' => 'website_lead',
        'special_name' => 'website_lead',
        'special_phone' => 'website_lead',
        'special_address' => 'website_lead',
        'trade' => 'website_lead_trades',
        'lead_type' => 'website_lead_types',
        'fandi' => 'website_lead_fandi'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'form_field',
        'map_field',
        'db_table',
        'details'
    ];

    /**
     * Return Table Name
     * 
     * @return type
     */
    public static function getTableName() {
        return self::TABLE_NAME;
    }

    /**
     * Get Nova Map Fields
     */
    public static function getNovaMapFields() {
        // Rewrite for Nova
        $mapFields = [];

        // Loop Field Map
        foreach(self::MAP_FIELDS as $type => $fields) {
            // Loop Type Fields
            foreach($fields as $column => $label) {
                $mapFields[$column] = [
                    'label' => $label,
                    'group' => self::MAP_TYPES[$type]
                ];
            }
        }

        // Return Map Fields
        return $mapFields;
    }

    /**
     * Get Unique Map Tables
     */
    public static function getUniqueMapTables() {
        // Rewrite for Nova
        $mapTables = [];

        // Loop Field Map
        foreach(self::MAP_TABLES as $table) {
            if(!in_array($table, $mapTables)) {
                $mapTables[] = $table;
            }
        }

        // Return Map Fields
        return $mapTables;
    }
}
