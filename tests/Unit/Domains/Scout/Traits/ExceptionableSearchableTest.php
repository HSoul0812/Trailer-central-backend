<?php

namespace Tests\Unit\Domains\Scout\Traits;

use App\Domains\Scout\Jobs\ExceptionableMakeSearchable;
use App\Exceptions\Tests\MissingTestDealerIdException;
use Bus;
use Laravel\Scout\Jobs\MakeSearchable;
use Tests\TestCase;
use Tests\Unit\Domains\Scout\stubs\ExceptionableSearchableModel;

class ExceptionableSearchableTest extends TestCase
{
    /**
     * In this test, we make sure that whenever the searchable method
     * got called, it dispatches our custom ExceptionableMakeSearchable
     * job instead of the MakeSearchableJob from the official scout library
     *
     * @group DMS
     * @group DMS_TRAITS
     *
     * @return void
     * @throws MissingTestDealerIdException
     */
    public function testItDispatchesTheExceptionableSearchableJob()
    {
        Bus::fake();

        ExceptionableSearchableModel::whereDealerId($this->getTestDealerId())->searchable();

        Bus::assertDispatched(ExceptionableMakeSearchable::class);
        Bus::assertNotDispatched(MakeSearchable::class);
    }
}
