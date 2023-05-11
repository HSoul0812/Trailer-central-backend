<?php

namespace Tests\Unit\Jobs\Files;

use App\Jobs\Files\DeleteS3FilesJob;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Str;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 *
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
        $files = [ Str::random() . '.txt' ];
        $deleteS3FilesJob = new DeleteS3FilesJob($files);

        $storage = Storage::fake('s3');

        Storage::shouldReceive('disk')
            ->with('s3')
            ->andReturn($storage)
            ->shouldReceive('delete')
            ->andThrow(new Exception('File does not exists.'));

        Log::shouldReceive('error')
            ->andReturn()
            ->shouldReceive('info')
            ->andReturn();

        $deleteS3FilesJob->handle();

        // If the code gets to this point, it means that all the mocked
        // expectations are passed, we want PHPUnit to do an assertion here,
        // so it doesn't show the R result in the test output
        // R means there is no assertion in this test case
        $this->assertTrue(true);
    }
}
