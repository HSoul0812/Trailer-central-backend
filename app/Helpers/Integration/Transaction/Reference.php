<?php

namespace App\Helpers\Integration\Transaction;

class Reference
{
    static private $_methodTranslation = array(
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

    static function isValidAction($action, $apiKey)
    {
        if(isset(self::$_methodTranslation[$apiKey])) {
            if(isset(self::$_methodTranslation[$apiKey][$action])) {
                return true;
            }
        }
        return false;
    }

    static function decodeAction($action, $apiKey)
    {
        if(isset(self::$_methodTranslation[$apiKey])) {
            if(isset(self::$_methodTranslation[$apiKey][$action])) {
                return self::$_methodTranslation[$apiKey][$action];
            }
        }
        return false;
    }

    static function translateEntityType($entityType, $apiKey)
    {
        $action = self::decodeAction($entityType, $apiKey);
        if(!empty($action)) {
            return $action['entity_type'];
        } else {
            return $entityType;
        }
    }

    static function getEntityFromReference($value, $entityType, $apiKey)
    {
        $db = TC_Db::getInstance();
        $entityType = self::translateEntityType($entityType, $apiKey);

        $select = $db->select()->from('api_entity_reference', 'entity_id');

        $value = $db->quote($value);
        $entityType = $db->quote($entityType);
        $apiKey = $db->quote($apiKey);

        $select->where("`reference_id` = $value");
        $select->where("`entity_type` = $entityType");
        $select->where("`api_key` = $apiKey");

        $result = $select->query()->fetchAll();
        if(count($result)) {
            return $result[0]['entity_id'];
        }
        return false;
    }

    static function getReferenceFromEntity($value, $entityType, $apiKey)
    {
        $db = TC_Db::getInstance();

        $select = $db->select()->from('api_entity_reference', 'reference_id');

        $value = $db->quote($value);
        $entityType = $db->quote($entityType);
        $apiKey = $db->quote($apiKey);

        $select->where("`entity_id` = $value");
        $select->where("`entity_type` = $entityType");
        $select->where("`api_key` = $apiKey");

        $result = $select->query()->fetchAll();
        if(count($result)) {
            return $result[0]['reference_id'];
        }
        return false;
    }
}
