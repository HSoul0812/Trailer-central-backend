<?php

namespace Tests\database\seeds\Inventory;

use App\Models\Inventory\Category;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryMfg;
use App\Models\Inventory\Manufacturers\Brand;
use App\Models\User\AuthToken;
use App\Models\User\DealerLocation;
use App\Models\User\DealerLocationMileageFee;
use App\Models\User\DealerUser;
use App\Models\User\DealerUserPermission;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * Class InventoryCreateSeeder
 * @package Tests\database\seeds\Inventory
 *
 * @property-read User $dealer
 * @property-read DealerUser $dealerUser
 * @property-read DealerUserPermission $dealerUserPermission
 * @property-read DealerLocation $dealerLocation
 * @property-read AuthToken $authToken
 * @property-read InventoryMfg $inventoryMfg
 * @property-read Brand $brand
 * @property-read Category $category
 * @property-read Inventory|null $inventory
 */
class InventorySeeder extends Seeder
{
    use WithGetter;

    private $fixedUser;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var DealerUser
     */
    private $dealerUser;

    /**
     * @var DealerUserPermission
     */
    private $dealerUserPermission;

    /**
     * @var DealerLocation
     */
    private $dealerLocation;

    /**
     * @var AuthToken
     */
    private $authToken;

    /**
     * @var InventoryMfg
     */
    private $inventoryMfg;

    /**
     * @var Brand
     */
    private $brand;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var string
     */
    private $userType;

    /**
     * @var array
     */
    private $permissions;

    /**
     * @var boolean
     */
    private $withInventory;

    /**
     * @var Inventory|null
     */
    private $inventory;

    /**
     * @var DealerLocationMileageFee|null
     */
    private $dealerLocationMileageFee;

    public function __construct(array $params = [])
    {
        $this->userType = $params['userType'] ?? AuthToken::USER_TYPE_DEALER;
        $this->permissions = $params['permissions'] ?? [];
        $this->withInventory = $params['withInventory'] ?? false;
    }

    public function seed(): void
    {
        $this->dealer = factory(User::class)->create();

        $this->dealerLocation = factory(DealerLocation::class)->create([
            'dealer_id' => $this->dealer->dealer_id
        ]);

        if ($this->userType === AuthToken::USER_TYPE_DEALER) {
            $this->authToken = factory(AuthToken::class)->create([
                'user_id' => $this->dealer->dealer_id,
                'user_type' => $this->userType,
            ]);
        } else {
            $this->dealerUser = factory(DealerUser::class)->create([
                'dealer_id' => $this->dealer->dealer_id
            ]);

            $this->authToken = factory(AuthToken::class)->create([
                'user_id' => $this->dealerUser->dealer_user_id,
                'user_type' => $this->userType,
            ]);

            foreach ($this->permissions as $permission) {
                factory(DealerUserPermission::class)->create([
                    'dealer_user_id' => $this->dealerUser->dealer_user_id,
                    'feature' => $permission['feature'],
                    'permission_level' => $permission['permission_level'],
                ]);
            }
        }

        $this->inventoryMfg = factory(InventoryMfg::class)->create();
        $this->brand = factory(Brand::class)->create();
        $this->category = factory(Category::class)->create();
        $this->dealerLocationMileageFee = factory(DealerLocationMileageFee::class)->create([
            'dealer_location_id' => $this->dealerLocation->getKey(),
            'inventory_category_id' => $this->category->getKey(),
        ]);

        if ($this->withInventory) {
            $inventoryParams = [
                'dealer_id' => $this->dealer->dealer_id,
                'dealer_location_id' => $this->dealerLocation->dealer_location_id,
                'manufacturer' => $this->inventoryMfg,
                'brand' => $this->brand,
                'category' => $this->category,
            ];

            $this->inventory = factory(Inventory::class)->create($inventoryParams);
        }
    }

    public function cleanUp(): void
    {
        InventoryMfg::destroy($this->inventoryMfg->id);
        Brand::destroy($this->brand->brand_id);
        Category::destroy($this->category->inventory_category_id);
        Inventory::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        DealerLocationMileageFee::destroy(['id' => $this->dealerLocationMileageFee->getKey()]);
        DealerLocation::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => $this->userType])->delete();

        if ($this->dealerUser) {
            DealerUserPermission::where(['dealer_user_id' => $this->dealerUser->dealer_user_id])->delete();
            DealerUser::destroy($this->dealerUser->dealer_user_id);
        }

        User::destroy($this->dealer->dealer_id);
    }

}
