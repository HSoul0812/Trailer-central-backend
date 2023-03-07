<?php

namespace Tests\Unit\Domains\Scout\Jobs;

use App\Domains\Scout\Jobs\ExceptionableMakeSearchable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class ExceptionableMakeSearchableTest extends TestCase
{
    /**
     * Test that the job throws exception if the collection is empty
     *
     * @group DMS
     * @group DMS_JOBS
     *
     * @return void
     */
    public function testItThrowsExceptionWhenThereIsNoModelInCollection()
    {
        $collection = new Collection([]);

        $job = new ExceptionableMakeSearchable($collection);

        $this->expectException(ModelNotFoundException::class);

        $job->handle();
    }
}
