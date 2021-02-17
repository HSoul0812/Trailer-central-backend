<?php

namespace App\Services\Quickbooks;

use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Dms\Quickbooks\ItemNewRepositoryInterface;

/**
 * Class NewItemService
 *
 * @package App\Services\Quickbooks
 */
class NewItemService
{

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ItemNewRepositoryInterface
     */
    private $itemNewRepository;

    public function __construct(UserRepositoryInterface $userRepository, ItemNewRepositoryInterface $itemNewRepository)
    {
        $this->userRepository = $userRepository;
        $this->itemNewRepository = $itemNewRepository;
    }

    public function getByItemName(int $dealerId, string $itemName)
    {
        $dealer = $this->userRepository->get(['dealer_id' => $dealerId]);
        
        return $this->itemNewRepository->get([
            'dealer_id' => $dealerId,
            'name' => $itemName,
            'is_default' => $dealer->is_default_quickbook_settings
        ]);
    }
}
