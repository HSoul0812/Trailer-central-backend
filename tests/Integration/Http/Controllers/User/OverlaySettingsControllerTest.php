<?php

namespace Tests\Integration\Http\Controllers\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User\AuthToken;
use App\Models\User\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Inventory\Inventory;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Inventory\GenerateOverlayImageJob;
use App\Models\Inventory\InventoryImage;
use App\Models\Inventory\Image;
use App\Models\User\DealerLocation;

class OverlaySettingsControllerTest extends TestCase {

    use WithFaker;

    /** @var User */
    protected $dealer;

    /** @var AuthToken */
    protected $token;

    const apiEndpoint = '/api/user/overlay/settings';

    public function setUp(): void
    {
        parent::setUp();

        $this->dealer = factory(User::class)->create([
            'type' => User::TYPE_DEALER,
            'state' => User::STATUS_ACTIVE
        ]);

        $this->token = $this->dealer->authToken->access_token;

        $this->location = factory(DealerLocation::class)->create([
            'dealer_id' => $this->dealer->getKey(),
        ]);

        Queue::fake();
    }

    public function tearDown(): void
    {
        Inventory::where('dealer_id', $this->dealer->getKey())->delete();

        $this->location->delete();

        $this->dealer->authToken->delete();

        $this->dealer->delete();

        parent::tearDown();
    }

    /**
     * @return array[]
     */
    public function overlayParamDataProvider()
    {
        return [[[
            'overlay_logo_position' => User::OVERLAY_LOGO_POSITION_LOWER_RIGHT, 
            'overlay_logo_width' => '200', 
            'overlay_logo_height' => '20%', 
            'overlay_upper' => User::OVERLAY_UPPER_DEALER_NAME, 
            'overlay_upper_bg' => '#000000', 
            'overlay_upper_alpha' => 0, 
            'overlay_upper_text' => '#ffffff', 
            'overlay_upper_size' => 40, 
            'overlay_upper_margin' => 40,
            'overlay_lower' => User::OVERLAY_UPPER_DEALER_PHONE, 
            'overlay_lower_bg' => '#000000', 
            'overlay_lower_alpha' => 0, 
            'overlay_lower_text' => '#ffffff', 
            'overlay_lower_size' => 40, 
            'overlay_lower_margin' => 40,
            'overlay_enabled' => Inventory::OVERLAY_ENABLED_ALL
        ]]];
    }

    /**
     * @dataProvider overlayParamDataProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testUpdateOverlaySettingsWithNoInventories($overlaySettings)
    {
        $overlaySettings['dealer_id'] = $this->dealer->dealer_id;

        $this->withHeaders(['access-token' => $this->token])
            ->postJson(self::apiEndpoint, $overlaySettings)
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'overlay_default',
                    'overlay_enabled',
                    'overlay_logo',
                    'overlay_logo_height',
                    'overlay_logo_position',
                    'overlay_logo_width',
                    'overlay_lower',
                    'overlay_lower_alpha',
                    'overlay_lower_bg',
                    'overlay_lower_margin',
                    'overlay_lower_size',
                    'overlay_lower_text',
                    'overlay_upper',
                    'overlay_upper_alpha',
                    'overlay_upper_bg',
                    'overlay_upper_margin',
                    'overlay_upper_size',
                    'overlay_upper_text'
                ]
            ]);

        $this->assertDatabaseHas(User::getTableName(), $overlaySettings);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
    }

    /**
     * @dataProvider overlayParamDataProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testUpdateOverlaySettingsWithLogo($overlaySettings)
    {
        $overlaySettings['dealer_id'] = $this->dealer->dealer_id;
        $overlaySettings['overlay_logo'] = UploadedFile::fake()->image('logo.png');

        $response = $this->withHeaders(['access-token' => $this->token])
            ->postJson(self::apiEndpoint, $overlaySettings)
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'overlay_default',
                    'overlay_enabled',
                    'overlay_logo',
                    'overlay_logo_height',
                    'overlay_logo_position',
                    'overlay_logo_width',
                    'overlay_lower',
                    'overlay_lower_alpha',
                    'overlay_lower_bg',
                    'overlay_lower_margin',
                    'overlay_lower_size',
                    'overlay_lower_text',
                    'overlay_upper',
                    'overlay_upper_alpha',
                    'overlay_upper_bg',
                    'overlay_upper_margin',
                    'overlay_upper_size',
                    'overlay_upper_text'
                ]
            ]);

        // get uploaded logo path
        $uploadedLogo = $overlaySettings['overlay_logo'];
        $logoPath = '/media/' . $this->dealer->dealer_id . '/logo-' . sha1_file($uploadedLogo);

        // confirm logo is uploaded to s3
        Storage::disk('s3')->assertExists([
            $logoPath
        ]);

        // replace with uploaded logo
        $overlaySettings['overlay_logo'] = Storage::disk('s3')->url($logoPath);

        // delete logo after done test
        Storage::disk('s3')->delete($logoPath);

        $this->assertDatabaseHas(User::getTableName(), $overlaySettings);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
    }

    /**
     * @dataProvider overlayParamDataProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testUpdateOverlaySettingsWithInventories($overlaySettings)
    {
        $overlaySettings['dealer_id'] = $this->dealer->dealer_id;

        $inventory = factory(Inventory::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'dealer_location_id' => $this->location->dealer_location_id
        ]);

        $images = factory(Image::class, 2)->create();

        $images->each(function (Image $image) use ($inventory): void {
            factory(InventoryImage::class)->create([
                'inventory_id' => $inventory->inventory_id,
                'image_id' => $image->image_id
            ]);
        });

        $this->withHeaders(['access-token' => $this->token])
            ->postJson(self::apiEndpoint, $overlaySettings)
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'overlay_default',
                    'overlay_enabled',
                    'overlay_logo',
                    'overlay_logo_height',
                    'overlay_logo_position',
                    'overlay_logo_width',
                    'overlay_lower',
                    'overlay_lower_alpha',
                    'overlay_lower_bg',
                    'overlay_lower_margin',
                    'overlay_lower_size',
                    'overlay_lower_text',
                    'overlay_upper',
                    'overlay_upper_alpha',
                    'overlay_upper_bg',
                    'overlay_upper_margin',
                    'overlay_upper_size',
                    'overlay_upper_text'
                ]
            ]);

        $this->assertDatabaseHas(User::getTableName(), $overlaySettings);

        Queue::assertPushed(GenerateOverlayImageJob::class);
    }

    /**
     * @dataProvider overlayParamDataProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testUpdateOverlaySettingsWithNoChanges($overlaySettings)
    {
        $overlaySettings['dealer_id'] = $this->dealer->dealer_id;

        $inventory = factory(Inventory::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'dealer_location_id' => $this->location->dealer_location_id
        ]);

        $images = factory(Image::class, 2)->create();

        $images->each(function (Image $image) use ($inventory): void {
            factory(InventoryImage::class)->create([
                'inventory_id' => $inventory->inventory_id,
                'image_id' => $image->image_id
            ]);
        });

        // update overlay settings before sending update request API
        foreach ($overlaySettings as $field => $value)
        {
            $this->dealer->$field = $value;
        }
        $this->dealer->save();

        $this->withHeaders(['access-token' => $this->token])
            ->postJson(self::apiEndpoint, $overlaySettings)
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'overlay_default',
                    'overlay_enabled',
                    'overlay_logo',
                    'overlay_logo_height',
                    'overlay_logo_position',
                    'overlay_logo_width',
                    'overlay_lower',
                    'overlay_lower_alpha',
                    'overlay_lower_bg',
                    'overlay_lower_margin',
                    'overlay_lower_size',
                    'overlay_lower_text',
                    'overlay_upper',
                    'overlay_upper_alpha',
                    'overlay_upper_bg',
                    'overlay_upper_margin',
                    'overlay_upper_size',
                    'overlay_upper_text'
                ]
            ]);

        $this->assertDatabaseHas(User::getTableName(), $overlaySettings);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
    }

    /**
     * @dataProvider overlayParamDataProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testUpdateOverlaySettingsWithNoInventoryImages($overlaySettings)
    {

        $overlaySettings['dealer_id'] = $this->dealer->dealer_id;

        $inventory = factory(Inventory::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'dealer_location_id' => $this->location->dealer_location_id
        ]);

        $this->assertDatabaseMissing(InventoryImage::getTableName(), [
            'inventory_id' => $inventory->getKey()
        ]);

        $this->withHeaders(['access-token' => $this->token])
            ->postJson(self::apiEndpoint, $overlaySettings)
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'overlay_default',
                    'overlay_enabled',
                    'overlay_logo',
                    'overlay_logo_height',
                    'overlay_logo_position',
                    'overlay_logo_width',
                    'overlay_lower',
                    'overlay_lower_alpha',
                    'overlay_lower_bg',
                    'overlay_lower_margin',
                    'overlay_lower_size',
                    'overlay_lower_text',
                    'overlay_upper',
                    'overlay_upper_alpha',
                    'overlay_upper_bg',
                    'overlay_upper_margin',
                    'overlay_upper_size',
                    'overlay_upper_text'
                ]
            ]);

        $this->assertDatabaseHas(User::getTableName(), $overlaySettings);

        Queue::assertNotPushed(GenerateOverlayImageJob::class);
    }

    /**
     * @dataProvider overlayParamDataProvider
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testUpdateOverlaySettingsWithOverlayenabled($overlaySettings)
    {
        $overlaySettings['dealer_id'] = $this->dealer->dealer_id;

        $inventory = factory(Inventory::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'dealer_location_id' => $this->location->dealer_location_id
        ]);

        // update overlay_enabled
        $inventory->update(['overlay_enabled' => 0]);

        // confirm change
        $this->assertDatabaseHas(Inventory::getTableName(), [
            'dealer_id' => $overlaySettings['dealer_id'],
            'overlay_enabled' => 0
        ]);

        $this->withHeaders(['access-token' => $this->token])
            ->postJson(self::apiEndpoint, $overlaySettings)
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'overlay_default',
                    'overlay_enabled',
                    'overlay_logo',
                    'overlay_logo_height',
                    'overlay_logo_position',
                    'overlay_logo_width',
                    'overlay_lower',
                    'overlay_lower_alpha',
                    'overlay_lower_bg',
                    'overlay_lower_margin',
                    'overlay_lower_size',
                    'overlay_lower_text',
                    'overlay_upper',
                    'overlay_upper_alpha',
                    'overlay_upper_bg',
                    'overlay_upper_margin',
                    'overlay_upper_size',
                    'overlay_upper_text'
                ]
            ]);

        $this->assertDatabaseHas(User::getTableName(), $overlaySettings);

        $this->assertDatabaseHas(Inventory::getTableName(), [
            'dealer_id' => $overlaySettings['dealer_id'],
            'overlay_enabled' => Inventory::OVERLAY_ENABLED_ALL
        ]);

        $this->assertDatabaseMissing(Inventory::getTableName(), [
            'dealer_id' => $overlaySettings['dealer_id'],
            'overlay_enabled' => 0
        ]);
    }
}