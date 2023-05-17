<?php

namespace Tests\Integration\App\Api\ViewsAndImpressions;

use Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\TestCase;

class DownloadTTAndAffiliateMonthlyCountingControllerTest extends TestCase
{
    public const ENDPOINT = '/api/views-and-impressions/tt-and-affiliate/download-zip';

    public function testItReturnsValidationErrorWhenRequiredParamsAreNotProvided()
    {
        $this
            ->get(self::ENDPOINT)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSee('The file path field is required.');
    }

    public function testItReturnsValidationErrorWhenFilePathIsInvalid()
    {
        $this
            ->get(self::ENDPOINT . '?file_path=1234/13/dealer-id-123.csv.gz')
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSee(
                value: "File 1234\/13\/dealer-id-123.csv.gz doesn't exist in the storage.",
                escape: false,
            );
    }

    public function testItReturnsStreamDownloadWhenFilePathIsValid()
    {
        $filePath = '2023/05/dealer-id-9999999.csv.gz';

        $storage = Storage::disk('monthly-inventory-impression-countings-reports');

        $storage->put($filePath, '');

        $this
            ->get(self::ENDPOINT . "?file_path=$filePath")
            ->assertOk()
            ->assertDownload('9999999-05-2023.csv.gz');

        $storage->delete($filePath);
    }
}
