<?php

namespace Tests\Unit\Jobs\Files;

use App\Jobs\Files\DeleteS3FilesJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test for App\Jobs\Files\DeleteS3FilesJob
 *
 * Class DeleteFilesJobTest
 * @package Tests\Unit\Jobs\Files
 *
 * @coversDefaultClass \App\Jobs\Files\DeleteS3FilesJob
 */
class DeleteS3FilesJobTest extends TestCase
{
    /**
     * @covers ::handle
     *
     * @group DMS
     * @group DMS_FILES
     */
    public function testHandle()
    {
        $files = ['/test/testFile1', '/test/testFile2'];
        $deleteS3FilesJob = new DeleteS3FilesJob($files);

        Storage::fake('s3');

        Storage::disk('s3')->put($files[0], 'some_content');
        Storage::disk('s3')->put($files[1], 'some_content');

        Storage::disk('s3')->assertExists($files);

        $deleteS3FilesJob->handle();

        Storage::disk('s3')->assertMissing($files);

        Log::shouldReceive('info')->with('Files have been successfully deleted');

        Storage::fake('s3');
    }

    /**
     * @covers ::handle
     *
     * @group DMS
     * @group DMS_FILES
     */
    public function testHandleWithException()
    {
        $files = [new \stdClass()];
        $deleteS3FilesJob = new DeleteS3FilesJob($files);

        Storage::fake('s3');

        $deleteS3FilesJob->handle();

        Log::shouldReceive('error');

        Storage::fake('s3');
    }
}
