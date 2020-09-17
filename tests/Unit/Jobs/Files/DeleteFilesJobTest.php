<?php

namespace Tests\Unit\Jobs\Files;

use App\Jobs\Files\DeleteFilesJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test for App\Jobs\Files\DeleteFilesJob
 *
 * Class DeleteFilesJobTest
 * @package Tests\Unit\Jobs\Files
 *
 * @coversDefaultClass \App\Jobs\Files\DeleteFilesJob
 */
class DeleteFilesJobTest extends TestCase
{
    /**
     * @covers ::handle
     */
    public function testHandle()
    {
        $files = ['/test/testFile1', '/test/testFile2'];
        $deleteFilesJob = new DeleteFilesJob($files);

        Storage::fake('s3');

        Storage::disk('s3')->put($files[0], 'some_content', 'public');
        Storage::disk('s3')->put($files[1], 'some_content', 'public');

        Storage::disk('s3')->assertExists($files);

        $deleteFilesJob->handle();

        Storage::disk('s3')->assertMissing($files);

        Log::shouldReceive('info')->with('Files have been successfully deleted');

        Storage::fake('s3');
    }

    /**
     * @covers ::handle
     */
    public function testHandleWithException()
    {
        $files = [new \stdClass()];
        $deleteFilesJob = new DeleteFilesJob($files);

        Storage::fake('s3');

        $deleteFilesJob->handle();

        Log::shouldReceive('error');

        Storage::fake('s3');
    }
}
