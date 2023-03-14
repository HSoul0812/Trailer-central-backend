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

    public function test_it_can_update_or_create_a_dealer_logo()
    {
        $benefit = Str::random(10);

        $this->assertDatabaseMissing(DealerLogo::getTableName(), [
            'dealer_id' => $this->dealerId,
            'benefit_statement' => $benefit
        ]);

        $logo = $this->dealerLogoRepository->update($this->dealerId, [
            'benefit_statement' => $benefit
        ]);

        $this->assertDatabaseHas(DealerLogo::getTableName(), [
            'dealer_id' => $this->dealerId,
            'benefit_statement' => $benefit
        ]);

        $logo->delete();
    }
}
