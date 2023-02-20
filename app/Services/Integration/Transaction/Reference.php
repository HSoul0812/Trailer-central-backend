<?php

namespace App\Services\Integration\Transaction;

use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Repositories\Feed\Mapping\Incoming\ApiEntityReferenceRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class Reference
 * @package App\Services\Integration\Transaction
 */
class Reference
{
    private $methodTranslation = array(
        'utc' => array(
            'addInventory' => array(
                'action' => 'add',
                'entity_type' => 'inventory'
            ),
            'modifyInventory' => array(
                'action' => 'update',
                'entity_type' => 'inventory'
            ),
            'removeInventory' => array(
                'action' => 'delete',
                'entity_type' => 'inventory'
            ),

            'addDealer' => array(
                'action' => 'add',
                'entity_type' => 'dealer'
            ),
            'modifyDealer' => array(
                'action' => 'update',
                'entity_type' => 'dealer'
            ),
            'deactivateDealer' => array(
                'action' => 'deactivate',
                'entity_type' => 'dealer'
            ),

            'addDealerLocation' => array(
                'action' => 'add',
                'entity_type' => 'dealer_location'
            ),
            'modifyDealerLocation' => array(
                'action' => 'update',
                'entity_type' => 'dealer_location'
            ),
        ),
        'pj' => array(
            'addInventory' => array(
                'action' => 'add',
                'entity_type' => 'inventory'
            ),
            'removeInventory' => array(
                'action' => 'delete',
                'entity_type' => 'inventory'
            ),
            'modifyInventory' => array(
                'action' => 'update',
                'entity_type' => 'inventory'
            ),
            'addDealer' => array(
                'action' => 'add',
                'entity_type' => 'dealer'
            ),
            'modifyDealer' => array(
                'action' => 'update',
                'entity_type' => 'dealer'
            ),
            'addDealerLocation' => array(
                'action' => 'add',
                'entity_type' => 'dealer_location'
            ),
            'modifyDealerLocation' => array(
                'action' => 'update',
                'entity_type' => 'dealer_location'
            )
        )
    );

    /**
     * @param $action
     * @param $apiKey
     * @return bool
     */
    public function isValidAction($action, $apiKey): bool
    {
        if(isset($this->methodTranslation[$apiKey])) {
            if(isset($this->methodTranslation[$apiKey][$action])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $action
     * @param $apiKey
     * @return false|string[]
     */
    public function decodeAction($action, $apiKey)
    {
        if(isset($this->methodTranslation[$apiKey])) {
            if(isset($this->methodTranslation[$apiKey][$action])) {
                return $this->methodTranslation[$apiKey][$action];
            }
        }
        return false;
    }

    function translateEntityType($entityType, $apiKey)
    {
        $action = $this->decodeAction($entityType, $apiKey);
        if(!empty($action)) {
            return $action['entity_type'];
        } else {
            return $entityType;
        }
    }

    /**
     * @param $value
     * @param $entityType
     * @param $apiKey
     * @return false|int
     * @throws BindingResolutionException
     */
    public function getEntityFromReference($value, $entityType, $apiKey)
    {
        /** @var ApiEntityReferenceRepositoryInterface $apiEntityReferenceRepository */
        $apiEntityReferenceRepository = app()->make(ApiEntityReferenceRepositoryInterface::class);

        $entityType = self::translateEntityType($entityType, $apiKey);

        /** @var ApiEntityReference $apiEntityReference */
        $apiEntityReference = $apiEntityReferenceRepository->get([
            'reference_id' => $value,
            'entity_type' => $entityType,
            'api_key' => $apiKey,
        ]);

        if (!($apiEntityReference ? $apiEntityReference->entity_id : false)) {
            print_r($apiKey);
            print_r($value);
            print_r($entityType);exit();
        }

        return $apiEntityReference ? $apiEntityReference->entity_id : false;
    }
}
