<?php

namespace Tests\Unit\App\DTOs\Dealer;

use App\DTOs\Dealer\PrivateDealerCheck;
use Tests\Common\TestCase;

class PrivateDealerCheckTest extends TestCase
{
    /**
     * @dataProvider privateDealerProvider
     */
    public function testPrivateDealer(array $dealer)
    {
        $checker = new PrivateDealerCheck();
        $this->assertTrue($checker->checkArray($dealer));
    }

    /**
     * @dataProvider regularDealerProvider
     */
    public function testRegularDealer(array $dealer)
    {
        $checker = new PrivateDealerCheck();
        $this->assertFalse($checker->checkArray($dealer));
    }

    public function privateDealerProvider()
    {
        return [
            [['id' => 8410]],
            [['id' => 1004]],
            [['id' => 12213]],
            [['id' => 10005]],
            [['id' => 1234, 'from' => 'trailertrader']],
        ];
    }

    public function regularDealerProvider()
    {
        return [
            [['id' => 353524]],
            [['id' => 1234, 'from' => 'trailercentral']],
        ];
    }
}
