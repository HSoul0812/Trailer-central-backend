<?php

namespace Tests\Integration\Repositories\User;

use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\User\DealerLogo;
use App\Repositories\User\DealerLogoRepositoryInterface;

/**
 * @group DW
 * @group DW_DEALER
 * @group DW_DEALER_LOGO
 */
class DealerLogoRepositoryTest extends TestCase
{
    /**
     * @var DealerLogoRepositoryInterface
     */
    private $dealerLogoRepository;
    private $dealerId;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerLogoRepository = $this->app->make(DealerLogoRepositoryInterface::class);
        $this->dealerId = $this->getTestDealerId();
    }

    public function test_it_can_create_a_dealer_logo()
    {
        $data = [
            'dealer_id' => $this->dealerId,
            'filename' => "dealer_logos/{$this->dealerId}_logo.png",
            'benefit_statement' => 'hello world'
        ];
        $logo = $this->dealerLogoRepository->create($data);

        $this->assertDatabaseHas(DealerLogo::getTableName(), $data);
        $logo->delete();
    }

    public function test_it_can_update_a_dealer_logo()
    {
        $benefit = Str::random(10);

        $logo = factory(DealerLogo::class)->create([
            'dealer_id' => $this->dealerId,
            'benefit_statement' => 'hello'
        ]);
        
        $this->assertDatabaseMissing(DealerLogo::getTableName(), [
            'dealer_id' => $this->dealerId,
            'benefit_statement' => $benefit
        ]);

        $this->dealerLogoRepository->update($this->dealerId, [
            'benefit_statement' => $benefit
        ]);

        $this->assertDatabaseHas(DealerLogo::getTableName(), [
            'dealer_id' => $this->dealerId,
            'benefit_statement' => $benefit
        ]);

        $logo->delete();
    }

    public function test_it_can_delete_a_dealer_logo()
    {
        factory(DealerLogo::class)->create([
            'dealer_id' => $this->dealerId
        ]);
        $this->assertDatabaseHas(DealerLogo::getTableName(), [
            'dealer_id' => $this->dealerId
        ]);

        $this->dealerLogoRepository->delete($this->dealerId);

        $this->assertDatabaseMissing(DealerLogo::getTableName(), [
            'dealer_id' => $this->dealerId
        ]);
    }

    public function test_it_can_retrieve_a_dealer_logo()
    {
        $createdLogo = factory(DealerLogo::class)->create([
            'dealer_id' => $this->dealerId
        ]);

        $retrievedLogo = $this->dealerLogoRepository->get($this->dealerId);
        $this->assertTrue($createdLogo->is($retrievedLogo));

        $createdLogo->delete();
    }
}
