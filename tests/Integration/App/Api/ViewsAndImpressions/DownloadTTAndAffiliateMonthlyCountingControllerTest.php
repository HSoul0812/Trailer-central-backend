<?php

namespace Tests\Integration\App\Api\ViewsAndImpressions;

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
}
