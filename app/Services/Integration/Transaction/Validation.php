<?php

namespace App\Services\Integration\Transaction;

use App\Models\Inventory\Inventory;
use App\Repositories\Feed\Mapping\Incoming\ApiEntityReferenceRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Class Validation
 * @package App\Services\Integration\Transaction
 */
class Validation
{
    private $apiKey = '';

    /**
     * @var Reference
     */
    private $reference;

    /**
     * @param Reference $reference
     */
    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
    }

    /**
     * @param $apiKey
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return false|mixed
     */
    public function getValidation()
    {
        if(!isset($this->validation[$this->apiKey])) {
            return false;
        }

        return $this->validation[$this->apiKey];
    }

    /**
     * @param $action
     * @return bool
     */
    public function isValidAction($action = null): bool
    {
        $validation = $this->getValidation();

        if(isset($validation[$action])) {
            if($this->reference->isValidAction($action, $this->getApiKey())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $value
     * @param $data
     * @param $format
     * @return array|mixed|string|string[]
     */
    private function getReferenceFormat($value, $data, $format)
    {
        foreach($data as $key => $value) {
            $format = str_replace('{{' . $key . '}}', $value, $format);
        }

        return $format;
    }

    /**
     * @param $action
     * @param $data
     * @param $i
     * @param $context
     * @return void
     * @throws BindingResolutionException
     */
    public function validateTransaction($action = null, $data = null, $i = 1, $context)
    {
        $validation = self::getValidation();

        /** @var InventoryRepositoryInterface $inventoryRepository */
        $inventoryRepository = app()->make(InventoryRepositoryInterface::class);
        /** @var ApiEntityReferenceRepositoryInterface $apiEntityReferenceRepository */
        $apiEntityReferenceRepository = app()->make(ApiEntityReferenceRepositoryInterface::class);

        foreach($validation[$action] as $key => $validation) {
            if(strpos($key, '/') == true) {
                $keys = explode('/', $key);

                $subsection = $data;
                $value = '';

                $q = 0;
                while(isset($keys[$q])) {
                    if(!isset($subsection[$keys[$q]])) {
                        break;
                    }
                    if(is_string($subsection[$keys[$q]])) {
                        $value = $subsection[$keys[$q]];
                        break;
                    }

                    $subsection = $subsection[$keys[$q]];
                    $q++;
                }

                $keyLabel = implode(".", $keys);
                $key = end($keys);

                if($q + 1 != count($keys)) {
                    $context->addTransactionError($i, "'$keyLabel' is a required attribute and was not defined or is empty.");
                    continue;
                }
            } else {
                $value = $data[$key] ?? '';
                $keyLabel = $key;
            }

            foreach($validation as $validationKey => $validationSettings) {
                switch($validationKey) {
                    case 'required':

                        $result = self::checkRequired($key, $value, $validationSettings);

                        //if 'required' is set to true, and the value is either not set, or empty
                        if(!$result) {
                            $context->addTransactionError($i, "'$keyLabel' is a required attribute and was not defined or is empty.");
                        }

                        break;

                    case 'validation':
                        $shouldBreak = ((((// if required is set...
                                        isset($validation['required']) && // ...and it is set to false...
                                        !$validation['required']) || (// ...or if required is not set at all...
                                    !isset($validation['required']))) && (// ...and the value is empty
                                !self::checkRequired($key, $value, true))// OR //
                            ) || (// if required is set...
                                isset($validation['required']) && // ...and it is set to true...
                                $validation['required'] && // ...and the value is empty
                                !self::checkRequired($key, $value, true)));

                        if($shouldBreak) {
                            break;
                        }

                        $result = $this->checkValidation($key, $value, $validationSettings);

                        if(!$result) {
                            //if it is an array of values to validate against
                            if(is_array($validationSettings)) {
                                $allowedValues = implode("', '", $validationSettings);

                                $context->addTransactionError($i,
                                    "'$value' is not a valid value for '$keyLabel'. It must be one of the following: '$allowedValues'.");
                            } else {
                                $context->addTransactionError($i,
                                    "'$value' is not a valid value for '$keyLabel'. It must match this regular expression: '$validationSettings'.");
                            }
                        }

                        break;

                    case 'unique':

                        $uniqueKey = $validation['unique_key'] ?? $key;

                        $table = $validation['unique_table'];
                        $useReference = $validation['use_reference'] ?? false;

                        $thisAction = $validation['reference_entity_type'] ?? $action;

                        $checkValue = isset($validation['reference_format']) ? self::getReferenceFormat($value, $data,
                            $validation['reference_format']) : $value;

                        if(!isset($validation['validation']) || $this->checkValidation($key, $value, $validation['validation'])) {
                            $result = $this->checkUnique($uniqueKey, $checkValue, $table, $thisAction, $useReference);

                            if(!$result && isset($validation['required']) && $validation['required']) {
                                $context->addTransactionError($i,
                                    "'$keyLabel' must have a unique value. A separate record with '$key' set to '$value' already exists.");
                            }
                        }

                        break;

                    case 'exists':

                        $existsKey = $validation['exists_key'] ?? $key;

                        $table = $validation['exists_table'];

                        $useReference = $validation['use_reference'] ?? false;

                        $thisAction = $validation['reference_entity_type'] ?? $action;

                        $checkValue = isset($validation['reference_format']) ? self::getReferenceFormat($value, $data,
                            $validation['reference_format']) : $value;

                        $result = $this->checkExists($existsKey, $checkValue, $table, $thisAction, $useReference);

                        if(!$result && isset($validation['required']) && $validation['required']) {
                            if($key == 'vin') {
                                try {
                                    /** @var Inventory $inventory */
                                    $inventory = $inventoryRepository->get(['vin' => $value]);

                                    $apiEntityReferenceRepository->create(array(
                                            'entity_id'    => $inventory->inventory_id,
                                            'reference_id' => $value,
                                            'entity_type'  => 'inventory',
                                            'api_key'      => 'pj'
                                    ));
                                } catch (ModelNotFoundException $e) {
                                    $context->addTransactionError($i, "The entity ('$value') referenced for '$keyLabel' does not exist.");
                                }
                            } else {
                                $context->addTransactionError($i, "The entity ('$value') referenced for '$keyLabel' does not exist.");
                            }
                        }

                        break;
                }
            }
        }
    }

    /**
     * @param $key
     * @param $value
     * @param $validationSettings
     * @return bool
     */
    static private function checkRequired($key, $value, $validationSettings): bool
    {
        //if validation is not set to true, or value is defined and not empty
        return !$validationSettings || (isset($value) && ($value==="0" || $value));
    }

    /**
     * @param $key
     * @param $value
     * @param $validationSettings
     * @return bool|int
     */
    private function checkValidation($key, $value, $validationSettings)
    {
        //if it is an array of values to validate against
        if(is_array($validationSettings)) {
            return in_array($value, $validationSettings);
        } //else just check regular expression
        else {
            return preg_match($validationSettings, $value);
        }
    }

    /**
     * @param $key
     * @param $value
     * @param $table
     * @param $action
     * @param $useReference
     * @return bool
     * @throws BindingResolutionException
     */
    private function checkUnique($key, $value, $table, $action, $useReference): bool
    {
        if($useReference) {
            $value = $this->reference->getEntityFromReference($value, $action, $this->getApiKey());

            if(empty($value)) {
                return true;
            }
        }

        return DB::table($table)->where([$key => $value])->count() <= 0;
    }

    /**
     * @param $key
     * @param $value
     * @param $table
     * @param $action
     * @param $useReference
     * @return bool
     * @throws BindingResolutionException
     */
    private function checkExists($key, $value, $table, $action, $useReference): bool
    {
        /** @var InventoryRepositoryInterface $inventoryRepository */
        $inventoryRepository = app()->make(InventoryRepositoryInterface::class);
        /** @var ApiEntityReferenceRepositoryInterface $apiEntityReferenceRepository */
        $apiEntityReferenceRepository = app()->make(ApiEntityReferenceRepositoryInterface::class);

        if($useReference) {
            try {
                /** @var Inventory $inventory */
                $inventory = $inventoryRepository->get(['vin' => $value]);
            } catch (ModelNotFoundException $e) {}

            $value = $this->reference->getEntityFromReference($value, $action, self::getApiKey());

            if(empty($value) && empty($inventory)) {
                return false;
            }

            if(empty($value) && !empty($inventory)) {
                // if the vin already exists, create entry and return true
                $apiEntityReferenceRepository->create(array(
                    'entity_id'    => $inventory->inventory_id,
                    'reference_id' => $value,
                    'entity_type'  => 'inventory',
                    'api_key'      => 'pj'
                ));
            }
        }

        return DB::table($table)->where([$key => $value])->count() > 0;
    }

    //VALIDATION KEYS

    //required   = boolean // whether or not the value is required
    //validation = regex|array // either a regular expression to test a value, or an array of values
    //unique     = boolean // whether or not the value needs to be unique
    //unique_key = string // the key to check against to determine whether or not the value is unique
    //unique_table = string // the table to check for uniqueness
    //exists     = boolean // whether or not the value must already exist
    //exists_key = string // the key to check against to determine whether or not the value already exists
    //exist_table = string // the table to check for existance

    //use_reference = true|false // used only in the case of unique / exists. should we run the key through the
    // api reference first before checking the table? The only time you will need
    // to do this is if we are checking the existence or uniqueness of an entity_id

    //reference_entity_type = string // the type of entity to check for in the reference table. the validation
    // by default will pull the entity type from the method name, but in some
    // cases you need to specify the entity type. For example, if you are adding
    // a dealer location, the method will define the entity as a dealer_location
    // but you still need to check for the existence of the dealer ID, which
    // is related to a dealer entity, not a dealer_location entity.

    //reference_format = string // if a specific format is used for the reference id. Can reference any variable
    // passed in the request for the related transaction. Usage example, a
    // manufacturer does not give globally unique ids to it's dealer locations. So
    // dealer 1 has location 1, location 2, and location 3, etc, and dealer 2 also
    // has location 1, location 2, location 3, etc. as a result, the manufacturer
    // will not pass globally unique location ids, which means we have to create our
    // own. we store the reference as {{dealer_id}}_{{location_id}}, which would come
    // out as something like 1234_1.

    private $validation = array(
        'utc' => array(
            'addInventory'         => array(
                'dealer_identifier'   => array(
                    'required'              => true,
                    'exists'                => true,
                    'exists_key'            => 'dealer_id',
                    'exists_table'          => 'dealer',
                    'reference_entity_type' => 'dealer',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'vin'                 => array(
                    'required'      => true,
                    'unique'        => true,
                    'unique_key'    => 'vin',
                    'unique_table'  => 'inventory',
                    'use_reference' => false,
                    /* 'validation'   => '/^[0-9A-HJ-NPR-Z]{17}$/', */
                ),
                'location_identifier' => array(
                    'required'              => true,
                    'exists'                => true,
                    'exists_key'            => 'dealer_location_id',
                    'exists_table'          => 'dealer_location',
                    'reference_entity_type' => 'dealer_location',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'year'                => array(
                    'required'   => true,
                    'validation' => '/^\d{4}$/',
                ),
                'brand'               => array(
                    'required'   => true,
                    'validation' => array('Haulmark', 'Wells Cargo', 'Featherlite', 'Exiss', 'Sooner'),
                ),
                'model'               => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'status'              => array(
                    'required'   => true,
                    'validation' => array('Available', 'Sold', 'On Order'),
                ),
                'length'              => array(
                    'required'   => true,
                    'validation' => '/.+/'
                ),
                'width'               => array(
                    'required'   => true,
                    'validation' => '/.+/'
                ),
                'height'              => array(
                    'validation' => '/.+/'
                ),
                'msrp'                => array(
                    'validation' => '/.+/'
                ),
                'axle_capacity'       => array(
                    'validation' => '/.+/'
                ),
                'axles'               => array(
                    /* 'required'   => true, */
                    'validation' => '/.+/'
                ),
                'gvwr'                => array(
                    /*'required'   => true, */
                    'validation' => '/.+/'
                ),
                'color'               => array(
                    'validation' => '/.+/'
                ),
                'hitch_type'          => array(
                    'required'   => true,
                    'validation' => array('bumper', 'fifth_wheel', 'gooseneck', 'pintle', 'tag', '5th wheel')
                ),
                'roof_type'           => array(
                    /*'required'   => true,*/
                    'validation' => array('round', 'flat')
                ),
                'nose_type'           => array(
                    /*'required'   => true,*/
                    'validation' => array('round', 'flat', 'v_front')
                ),
                'description'         => array(
                    'validation' => '/.+/'
                )
            ),
            'modifyInventory'      => array(
                'dealer_identifier'   => array(
                    'required'              => true,
                    'exists'                => true,
                    'exists_key'            => 'dealer_id',
                    'exists_table'          => 'dealer',
                    'reference_entity_type' => 'dealer',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'vin'                 => array(
                    'required'      => true,
                    'exists'        => true,
                    'exists_key'    => 'inventory_id',
                    'exists_table'  => 'inventory',
                    'use_reference' => true,
                    /*'validation'    => '/^[0-9A-HJ-NPR-Z]{17}$/',*/
                ),
                'location_identifier' => array(
                    'exists'                => true,
                    'exists_key'            => 'dealer_location_id',
                    'exists_table'          => 'dealer_location',
                    'reference_entity_type' => 'dealer_location',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'year'                => array(
                    'validation' => '/^\d{4}$/',
                ),
                'brand'               => array(
                    'validation' => array('Haulmark', 'Wells Cargo', 'Featherlite', 'Exiss', 'Sooner'),
                ),
                'model'               => array(
                    'validation' => '/.+/',
                ),
                'status'              => array(
                    'validation' => array('Available', 'Sold', 'On Order'),
                ),
                'length'              => array(
                    'validation' => '/.+/'
                ),
                'width'               => array(
                    'validation' => '/.+/'
                ),
                'height'              => array(
                    'validation' => '/.+/'
                ),
                'msrp'                => array(
                    'validation' => '/.+/'
                ),
                'axle_capacity'       => array(
                    'validation' => '/.+/'
                ),
                'axles'               => array(
                    'validation' => '/.+/'
                ),
                'gvwr'                => array(
                    'validation' => '/.+/'
                ),
                'color'               => array(
                    'validation' => '/.+/'
                ),
                'hitch_type'          => array(
                    'validation' => array('bumper', 'fifth_wheel', 'gooseneck', 'pintle', 'tag', '5th wheel')
                ),
                'roof_type'           => array(
                    'validation' => array('round', 'flat')
                ),
                'nose_type'           => array(
                    'validation' => array('round', 'flat', 'v_front')
                ),
                'description'         => array(
                    'validation' => '/.+/'
                )
            ),
            'addDealer'            => array(
                'name'                => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'email'               => array(
                    'required'     => true,
                    /*'unique'       => true, let's not make this unique for now*/
                    'unique_key'   => 'email',
                    'unique_table' => 'dealer',
                    'validation'   => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/ui',
                ),
                'identifier'          => array(
                    'required'      => true,
                    /*'unique'        => true, */
                    'unique_key'    => 'dealer_id',
                    'unique_table'  => 'dealer',
                    'use_reference' => true,
                    'validation'    => '/.+/',
                ),
                'location/identifier' => array(
                    'required'              => true,
                    'unique'                => true,
                    'unique_key'            => 'dealer_location_id',
                    'unique_table'          => 'dealer_location',
                    'reference_entity_type' => 'dealer_location',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'location/name'       => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/contact'    => array(
                    'validation' => '/.+/',
                ),
                'location/website'    => array(
                    'validation' => '/.+/',
                ),
                'location/phone'      => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/email'      => array(
                    'required'   => true,
                    'validation' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/ui',
                ),
                'location/address'    => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/city'       => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/region'     => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/country'    => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/postalcode' => array(
                    'required'   => true,
                    'validation' => '/.+/',
                )
            ),
            'modifyDealer'         => array(
                'name'       => array(
                    'validation' => '/.+/',
                ),
                'identifier' => array(
                    'required'      => true,
                    'exists'        => true,
                    'exists_key'    => 'dealer_id',
                    'exists_table'  => 'dealer',
                    'use_reference' => true,
                    'validation'    => '/.+/',
                )
            ),
            'deactivateDealer'     => array(
                'name'       => array(
                    'validation' => '/.+/',
                ),
                'identifier' => array(
                    'required'      => true,
                    'exists'        => true,
                    'exists_key'    => 'dealer_id',
                    'exists_table'  => 'dealer',
                    'use_reference' => true,
                    'validation'    => '/.+/',
                )
            ),
            'addDealerLocation'    => array(
                'dealer_identifier' => array(
                    'required'              => true,
                    'exists'                => true,
                    'exists_key'            => 'dealer_id',
                    'reference_entity_type' => 'dealer',
                    'exists_table'          => 'dealer',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'identifier'        => array(
                    'required'              => true,
                    'unique'                => true,
                    'unique_key'            => 'dealer_location_id',
                    'unique_table'          => 'dealer_location',
                    'reference_entity_type' => 'dealer_location',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'name'              => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'contact'           => array(
                    'validation' => '/.+/',
                ),
                'website'           => array(
                    'validation' => '/.+/',
                ),
                'phone'             => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'email'             => array(
                    'validation' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/ui',
                ),
                'address'           => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'city'              => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'region'            => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'country'           => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'postalcode'        => array(
                    'required'   => true,
                    'validation' => '/.+/',
                )
            ),
            'modifyDealerLocation' => array(
                'dealer_identifier' => array(
                    'required'              => true,
                    'exists'                => true,
                    'exists_key'            => 'dealer_id',
                    'reference_entity_type' => 'dealer',
                    'exists_table'          => 'dealer',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'identifier'        => array(
                    'required'      => true,
                    'exists'        => true,
                    'exists_key'    => 'dealer_location_id',
                    'exists_table'  => 'dealer_location',
                    'use_reference' => true,
                    'validation'    => '/.+/',
                ),
                'name'              => array(
                    'validation' => '/.+/',
                ),
                'contact'           => array(
                    'validation' => '/.+/',
                ),
                'website'           => array(
                    'validation' => '/.+/',
                ),
                'phone'             => array(
                    'validation' => '/.+/',
                ),
                'email'             => array(
                    'validation' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/ui',
                ),
                'address'           => array(
                    'validation' => '/.+/',
                ),
                'city'              => array(
                    'validation' => '/.+/',
                ),
                'region'            => array(
                    'validation' => '/.+/',
                ),
                'country'           => array(
                    'validation' => '/.+/',
                ),
                'postalcode'        => array(
                    'validation' => '/.+/',
                )
            )
        ),
        'pj'  => array(
            'addInventory'         => array(
                'dealer_identifier'   => array(
                    'required'              => true,
                    'exists'                => true,
                    'exists_key'            => 'dealer_id',
                    'exists_table'          => 'dealer',
                    'reference_entity_type' => 'dealer',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'vin'                 => array(
                    'required'      => true,
                    'unique'        => true,
                    'unique_key'    => 'vin',
                    'unique_table'  => 'inventory',
                    'use_reference' => false,
                    'validation'    => '/.+/',
                    //'validation'   => '/^[0-9A-HJ-NPR-Z]{17}$/',
                ),
                'year'                => array(
                    'required'   => true,
                    'validation' => '/^\d{4}$/',
                ),
                'category'            => array(
                    'required'   => true,
                    'validation' => array(
                        'camping_rv',
                        'cargo_enclosed',
                        'car_racing',
                        'dump',
                        'flatbed',
                        'motorcycle',
                        'other',
                        'snowmobile',
                        'stock_stock-combo',
                        'toy',
                        'utility',
                        'vending_concession',
                        'watercraft',
                        'atv',
                        'bed_equipment',
                        'tow_dolly',
                        'equipment'
                    )
                ),
                'model'               => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location_identifier' => array(
                    'required'              => true,
                    'exists'                => true,
                    'exists_key'            => 'dealer_location_id',
                    'exists_table'          => 'dealer_location',
                    'reference_entity_type' => 'dealer_location',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'status'              => array(
                    'required'   => true,
                    'validation' => array('sold', 'available', 'on_order')
                ),
                'length'              => array(
                    'required'   => true,
                    'validation' => '/.+/'
                ),
                'width'               => array(
                    'validation' => '/.+/'
                ),
                'height'              => array(
                    'validation' => '/.+/'
                ),
                'msrp'                => array(
                    'validation' => '/.+/'
                ),
                'axle_capacity'       => array(
                    'required'   => true,
                    'validation' => '/.+/'
                ),
                'axles'               => array(
                    'required'   => true,
                    'validation' => '/.+/'
                ),
                'gvwr'                => array(
                    'required'   => true,
                    'validation' => '/.+/'
                ),
                'color'               => array(
                    'validation' => '/.+/'
                ),
                'hitch_type'          => array(
                    'required'   => true,
                    'validation' => array('BP', 'GN', 'PT', 'SD')
                ),
                'roof_type'           => array(
                    'validation' => array('Round', 'Flat')
                ),
                'nose_type'           => array(
                    'validation' => array('V Front', 'Flat')
                ),
                'description'         => array(
                    'validation' => '/.+/'
                )
            ),
            'modifyInventory'      => array(
                'dealer_identifier'   => array(
                    'required'              => true,
                    'exists'                => true,
                    'exists_key'            => 'dealer_id',
                    'exists_table'          => 'dealer',
                    'reference_entity_type' => 'dealer',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'vin'                 => array(
                    'required'      => true,
                    'exists'        => true,
                    'exists_key'    => 'inventory_id',
                    'exists_table'  => 'inventory',
                    'use_reference' => true,
                    'validation'    => '/.+/',
                    //'validation'    => '/^[0-9A-HJ-NPR-Z]{17}$/',
                ),
                'year'                => array(
                    'validation' => '/^\d{4}$/',
                ),
                'category'            => array(
                    'validation' => array(
                        'camping_rv',
                        'cargo_enclosed',
                        'car_racing',
                        'dump',
                        'flatbed',
                        'motorcycle',
                        'other',
                        'snowmobile',
                        'stock_stock-combo',
                        'toy',
                        'utility',
                        'vending_concession',
                        'watercraft',
                        'atv',
                        'bed_equipment',
                        'tow_dolly',
                        'equipment'
                    )
                ),
                'model'               => array(
                    'validation' => '/.+/',
                ),
                'location_identifier' => array(
                    'exists'                => true,
                    'exists_key'            => 'dealer_location_id',
                    'exists_table'          => 'dealer_location',
                    'reference_entity_type' => 'dealer_location',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'status'              => array(
                    'validation' => array('sold', 'available', 'on_order')
                ),
                'length'              => array(
                    'validation' => '/.+/'
                ),
                'width'               => array(
                    'validation' => '/.+/'
                ),
                'height'              => array(
                    'validation' => '/.+/'
                ),
                'msrp'                => array(
                    'validation' => '/.+/'
                ),
                'axle_capacity'       => array(
                    'validation' => '/.+/'
                ),
                'axles'               => array(
                    'validation' => '/.+/'
                ),
                'gvwr'                => array(
                    'validation' => '/.+/'
                ),
                'color'               => array(
                    'validation' => '/.+/'
                ),
                'hitch_type'          => array(
                    'validation' => array('BP', 'GN', 'PT', 'SD')
                ),
                'roof_type'           => array(
                    'validation' => array('Round', 'Flat')
                ),
                'nose_type'           => array(
                    'validation' => array('V Front', 'Flat')
                ),
                'description'         => array(
                    'validation' => '/.+/'
                )
            ),
            'removeInventory'      => array(
                'dealer_identifier' => array(
                    'required'              => true,
                    'exists'                => true,
                    'exists_key'            => 'dealer_id',
                    'exists_table'          => 'dealer',
                    'reference_entity_type' => 'dealer',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'vin'               => array(
                    'required'      => true,
                    'exists'        => true,
                    'exists_key'    => 'inventory_id',
                    'exists_table'  => 'inventory',
                    'use_reference' => true,
                    'validation'    => '/^[0-9A-HJ-NPR-Z]{17}$/',
                ),
            ),
            'addDealer'            => array(
                'name'                => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'email'               => array(
                    /*'required'     => true,*/
                    /*'unique'       => true,*/
                    'unique_key'   => 'email',
                    'unique_table' => 'dealer',
                    'validation'   => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/ui',
                ),
                'identifier'          => array(
                    'required'      => true,
                    'unique'        => true,
                    'unique_key'    => 'dealer_id',
                    'unique_table'  => 'dealer',
                    'use_reference' => true,
                    'validation'    => '/.+/',
                ),
                'location/identifier' => array(
                    'required'              => true,
                    'unique'                => true,
                    'unique_key'            => 'dealer_location_id',
                    'unique_table'          => 'dealer_location',
                    'reference_entity_type' => 'dealer_location',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'location/name'       => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/contact'    => array(
                    'validation' => '/.+/',
                ),
                'location/website'    => array(
                    'validation' => '/.+/',
                ),
                'location/phone'      => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/email'      => array(
                    /*'required'     => true,*/
                    'validation' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/ui',
                ),
                'location/address'    => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/city'       => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/region'     => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/country'    => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'location/postalcode' => array(
                    'required'   => true,
                    'validation' => '/.+/',
                )
            ),
            'modifyDealer'         => array(
                'name'       => array(
                    'validation' => '/.+/',
                ),
                'identifier' => array(
                    'required'      => true,
                    'exists'        => true,
                    'exists_key'    => 'dealer_id',
                    'exists_table'  => 'dealer',
                    'use_reference' => true,
                    'validation'    => '/.+/',
                )
            ),
            'addDealerLocation'    => array(
                'dealer_identifier' => array(
                    'required'              => true,
                    'exists'                => true,
                    'exists_key'            => 'dealer_id',
                    'reference_entity_type' => 'dealer',
                    'exists_table'          => 'dealer',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'identifier'        => array(
                    'required'              => true,
                    'unique'                => true,
                    'unique_key'            => 'dealer_location_id',
                    'unique_table'          => 'dealer_location',
                    'reference_entity_type' => 'dealer_location',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'name'              => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'contact'           => array(
                    'validation' => '/.+/',
                ),
                'website'           => array(
                    'validation' => '/.+/',
                ),
                'phone'             => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'email'             => array(
                    'validation' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/ui',
                ),
                'address'           => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'city'              => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'region'            => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'country'           => array(
                    'required'   => true,
                    'validation' => '/.+/',
                ),
                'postalcode'        => array(
                    'required'   => true,
                    'validation' => '/.+/',
                )
            ),
            'modifyDealerLocation' => array(
                'dealer_identifier' => array(
                    'required'              => true,
                    'exists'                => true,
                    'exists_key'            => 'dealer_id',
                    'reference_entity_type' => 'dealer',
                    'exists_table'          => 'dealer',
                    'use_reference'         => true,
                    'validation'            => '/.+/',
                ),
                'identifier'        => array(
                    'required'      => true,
                    'exists'        => true,
                    'exists_key'    => 'dealer_location_id',
                    'exists_table'  => 'dealer_location',
                    'use_reference' => true,
                    'validation'    => '/.+/',
                ),
                'name'              => array(
                    'validation' => '/.+/',
                ),
                'contact'           => array(
                    'validation' => '/.+/',
                ),
                'website'           => array(
                    'validation' => '/.+/',
                ),
                'phone'             => array(
                    'validation' => '/.+/',
                ),
                'email'             => array(
                    'validation' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/ui',
                ),
                'address'           => array(
                    'validation' => '/.+/',
                ),
                'city'              => array(
                    'validation' => '/.+/',
                ),
                'region'            => array(
                    'validation' => '/.+/',
                ),
                'country'           => array(
                    'validation' => '/.+/',
                ),
                'postalcode'        => array(
                    'validation' => '/.+/',
                )
            )
        )
    );
}
