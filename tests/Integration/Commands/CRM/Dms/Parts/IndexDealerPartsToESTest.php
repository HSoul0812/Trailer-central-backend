<?php

namespace Tests\Integration\Commands\CRM\Dms\Parts;

use App\Console\Commands\CRM\Dms\Parts\IndexDealerPartsToES;
use App\Domains\Parts\Actions\IndexDealerPartsToESAction;
use App\Models\User\User;
use Artisan;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class IndexDealerPartsToESTest extends TestCase
{
    /**
     * Test that it can process with the correct dealers
     *
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testItCanProcessWithCorrectDealers()
    {
        $dealers = factory(User::class, 2)->create();

        $dealerIds = $dealers->pluck('dealer_id')->implode(',');

        $this->instance(
            IndexDealerPartsToESAction::class,
            Mockery::mock(IndexDealerPartsToESAction::class, function (MockInterface $mock) use ($dealers) {
                $mock->shouldReceive('withChunkSize')->with(80)->once()->andReturn($mock);
                $mock->shouldReceive('withDelayChunkThreshold')->with(100)->once()->andReturn($mock);
                $mock->shouldReceive('withDelay')->with(15)->once()->andReturn($mock);
                $mock->shouldReceive('withOnDealerHasNoParts')->once()->andReturn($mock);
                $mock->shouldReceive('withOnStartProcessingRound')->once()->andReturn($mock);
                $mock->shouldReceive('withOnDispatchedJobs')->once()->andReturn($mock);
                $mock->shouldReceive('withOnDispatchedExceedingThreshold')->once()->andReturn($mock);

                // We make sure that the execute method got called once for each dealer id
                foreach (range(0, count($dealers) - 1) as $index) {
                    $mock->shouldReceive('execute')->with(Mockery::on(function (User $dealer) use ($dealers, $index) {
                        return $dealer->dealer_id === $dealers->get($index)->dealer_id;
                    }))->once()->andReturn();
                }
            })
        );

        $return = Artisan::call(IndexDealerPartsToES::class, [
            'dealerIds' => $dealerIds,
            '--chunkSize' => '80',
            '--delayChunkThreshold' => '100',
            '--delay' => '15',
        ]);

        $this->assertEquals(0, $return);
    }

    /**
     * Test that it won't process the invalid dealer id
     *
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testItWillNotProcessInvalidDealerIds()
    {
        Artisan::call(IndexDealerPartsToES::class, [
            'dealerIds' => 'just,some,random,string',
        ]);

        $output = Artisan::output();

        $this->assertTrue(Str::contains($output, "Invalid dealer ID format: just"));
        $this->assertTrue(Str::contains($output, "Invalid dealer ID format: some"));
        $this->assertTrue(Str::contains($output, "Invalid dealer ID format: random"));
        $this->assertTrue(Str::contains($output, "Invalid dealer ID format: string"));
    }
}
