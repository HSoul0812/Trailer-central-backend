<?php

namespace App\Services\Integration\Transaction\Adapter;

use App\Repositories\Feed\Mapping\Incoming\ApiEntityReferenceRepositoryInterface;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Integration\Transaction\Reference;
use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class Adapter
 * @package App\Services\Integration\Transaction\Adapter
 */
abstract class Adapter
{
    protected $apiKey = '';
    protected $_entityType = '';
    protected $_conversions = array();

    public const ADAPTER_MAPPING = [
        'Adapter_Utc_Inventory' => 'App\Services\Integration\Transaction\Adapter\Utc\Inventory',
        'Adapter_Pj_Inventory' => 'App\Services\Integration\Transaction\Adapter\Pj\Inventory'
    ];

    /**
     * @var ApiEntityReferenceRepositoryInterface
     */
    protected $apiEntityReferenceRepository;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var InventoryServiceInterface
     */
    protected $inventoryService;

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;

    /**
     * @var Reference
     */
    protected $reference;

    public function __construct(
        ApiEntityReferenceRepositoryInterface $apiEntityReferenceRepository,
        UserRepositoryInterface $userRepository,
        AttributeRepositoryInterface $attributeRepository,
        InventoryServiceInterface $inventoryService,
        InventoryRepositoryInterface $inventoryRepository,
        Reference $reference
    ) {
        $this->apiEntityReferenceRepository = $apiEntityReferenceRepository;
        $this->attributeRepository = $attributeRepository;
        $this->userRepository = $userRepository;
        $this->inventoryService = $inventoryService;
        $this->inventoryRepository = $inventoryRepository;
        $this->reference = $reference;
    }

    /**
     * @param $entityType
     * @param $referenceId
     * @return false|int
     * @throws BindingResolutionException
     */
    public function getEntityFromReference($entityType = null, $referenceId = null)
    {
        return $this->reference->getEntityFromReference($referenceId, $entityType, $this->apiKey);
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
            'api_key'      => $this->apiKey
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

    /**
     * @param string $entityType
     * @param array $attributes
     * @return array
     */
    protected function getInventoryAttributes(string $entityType, array $attributes): array
    {
        $defaultAttributes = $this->attributeRepository
            ->getAllByEntityTypeId($entityType)
            ->pluck('attribute_id', 'code')
            ->toArray();

        $inventoryAttributes = [];

        foreach ($attributes as $name => $value) {
            if (!isset($defaultAttributes[$name])) {
                continue;
            }

            $inventoryAttributes[] = [
                'attribute_id' => $defaultAttributes[$name],
                'value' => $value,
            ];
        }

        return $inventoryAttributes;
    }
}
