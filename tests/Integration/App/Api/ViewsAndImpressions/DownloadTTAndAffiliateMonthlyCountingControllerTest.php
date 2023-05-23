<?php

namespace Tests\Integration\App\Api\ViewsAndImpressions;

use App\Models\AppToken;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\TestCase;

class DownloadTTAndAffiliateMonthlyCountingControllerTest extends TestCase
{
    public const ENDPOINT = '/api/views-and-impressions/tt-and-affiliate/download-zip';

    public function testItReturnsValidationErrorWhenRequiredParamsAreNotProvided()
    {
        $appToken = AppToken::factory()->create();

        $this
            ->get(self::ENDPOINT . "?app-token=$appToken->token")
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSee('The file path field is required.');
    }

    public function testItReturnsValidationErrorWhenFilePathIsInvalid()
    {
        $appToken = AppToken::factory()->create();

        $this
            ->get(self::ENDPOINT . "?file_path=1234/13/dealer-id-123.csv.gz&app-token=$appToken->token")
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSee(
                value: "File 1234\/13\/dealer-id-123.csv.gz doesn't exist in the storage.",
                escape: false,
            );
    }
}
