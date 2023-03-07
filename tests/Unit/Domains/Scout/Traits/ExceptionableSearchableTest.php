<?php

namespace Tests\Unit\Domains\Scout\Traits;

use App\Domains\Scout\Jobs\ExceptionableMakeSearchable;
use App\Exceptions\Tests\MissingTestDealerIdException;

use App\Models\Parts\Part;
use Bus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Scout\Jobs\MakeSearchable;
use Tests\TestCase;

class ExceptionableSearchableTest extends TestCase
{
    use WithFaker;

    /**
     * @var Part[]|Collection<Part>
     */
    private $seed;

    /**
     * In this test, we make sure that whenever the searchable method
     * got called, it dispatches our custom ExceptionableMakeSearchable
     * job instead of the MakeSearchableJob from the official scout library
     *
     * @covers ExceptionableSearchable::queueMakeSearchable()
     *
     * @group DMS
     * @group DMS_TRAITS
     *
     * @return void
     * @throws MissingTestDealerIdException
     */
    public function testItDispatchesTheExceptionableSearchableJob(): void
    {
        Bus::fake();

        // we're using `Part` model as stub
        Part::query()
            ->whereIn('sku', $this->seed->pluck('sku')->toArray())
            ->searchable();

        Bus::assertDispatched(ExceptionableMakeSearchable::class);
        Bus::assertNotDispatched(MakeSearchable::class);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seed = factory(Part::class, 2)->create();
    }

    public function tearDown(): void
    {
        Part::query()
            ->whereIn('sku', $this->seed->pluck('sku')->toArray())
            ->delete();

        parent::tearDown();
    }
}
