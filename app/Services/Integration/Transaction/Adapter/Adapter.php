<?php

namespace App\Services\Integration\Transaction\Adapter;

use App\Repositories\Feed\Mapping\Incoming\ApiEntityReferenceRepositoryInterface;
use App\Services\Integration\Transaction\Reference;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class Adapter
 * @package App\Services\Integration\Transaction\Adapter
 */
abstract class Adapter
{
    protected $_apiKey = '';
    protected $_entityType = '';
    protected $_conversions = array();

    public const ADAPTER_MAPPING = [
        'Adapter_Utc_Inventory' => 'App\Services\Integration\Transaction\Adapter\Utc\Inventory'
    ];

    /**
     * @var ApiEntityReferenceRepositoryInterface
     */
    protected $apiEntityReferenceRepository;

    public function __construct(ApiEntityReferenceRepositoryInterface $apiEntityReferenceRepository)
    {
        $this->apiEntityReferenceRepository = $apiEntityReferenceRepository;
    }

    /**
     * @param $entityType
     * @param $referenceId
     * @return false|int
     * @throws BindingResolutionException
     */
    public function getEntityFromReference($entityType = null, $referenceId = null)
    {
        return Reference::getEntityFromReference($referenceId, $entityType, $this->_apiKey);
    }

    /**
     * @param $entityId
     * @param $referenceId
     * @return void
     */
    public function saveReference($entityId = null, $referenceId = null)
    {
        $this->apiEntityReferenceRepository->create(array(
            'entity_id'    => $entityId,
            'reference_id' => $referenceId,
            'entity_type'  => $this->_entityType,
            'api_key'      => $this->_apiKey
        ));
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function convert($attribute, $value)
    {
        if(!isset($this->_conversions[$attribute])) {
            return $value;
        }

        if(isset($this->_conversions[$attribute][$value])) {
            return $this->_conversions[$attribute][$value];
        }

        return $value;
    }
}
