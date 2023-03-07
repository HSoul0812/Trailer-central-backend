<?php

namespace Tests\Feature\Inventory\Image;

use App\Models\Inventory\CustomOverlay;
use App\Models\Inventory\Image;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryImage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;
use App\Models\User\User;
use App\Models\User\AuthToken;

class EndpointInventoryImageTest extends TestCase
{
    protected const VERB = '';
    protected const ENDPOINT = '';

    use WithFaker;

    protected function itShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->json(static::VERB, str_replace(':id', $this->faker->randomDigit, static::ENDPOINT))
            ->assertStatus(403);
    }

    /** @var array{dealer: User, overlays: array<CustomOverlay>, token: AuthToken} */
    protected $seed;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed = $this->createDealerAndInventoryWithImages();
    }

    public function tearDown(): void
    {
        $this->tearDownSeed($this->seed['dealer']->dealer_id);

        parent::tearDown();
    }

    protected function tearDownSeed(int $dealerId): void
    {
        Image::query()
            ->join('inventory_image', 'inventory_image.image_id', '=', 'image.image_id')
            ->join('inventory', 'inventory.inventory_id', '=', 'inventory_image.inventory_id')
            ->where('dealer_id', $dealerId)->delete();

        AuthToken::query()->where('user_id', $dealerId)->delete();
        Inventory::query()->where('dealer_id', $dealerId)->delete();
        User::query()->where('dealer_id', $dealerId)->delete();
    }

    /**
     * @return array{dealer: User, images: Collection<Image>, token: AuthToken, inventory: Inventory}
     */
    protected function createDealerAndInventoryWithImages(): array
    {
        $dealer = factory(User::class)->create();

        $token = factory(AuthToken::class)->create([
            'user_id' => $dealer->dealer_id,
            'user_type' => 'dealer',
        ]);

        $inventory = factory(Inventory::class)->create([
            'dealer_id' => $dealer->dealer_id
        ]);

        $images = factory(Image::class, 3)->create();

        $images->each(function (Image $image) use ($inventory): void {
            factory(InventoryImage::class)->create([
                'inventory_id' => $inventory->inventory_id,
                'image_id' => $image->image_id
            ]);
        });

        return [
            'dealer' => $dealer,
            'token' => $token,
            'inventory' => $inventory,
            'images' => $images
        ];
    }
}
