<?php

namespace Tests\Unit\Transformers\User;

use App\Models\User\DealerLogo;
use App\Transformers\User\DealerLogoTransformer;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_DEALER
 * @group DW_DEALER_LOGO
 */
class DealerLogoTransformerTest extends TestCase
{
    private $dealerLogo;
    private $transformer;

    public function setUp(): void
    {
        parent::setUp();
        $dealerId = $this->getTestDealerId();

        $this->dealerLogo = new DealerLogo([
            'dealer_id' => $dealerId,
            'filename' => "dealer_logos/{$dealerId}_logo.png",
            'benefit_statement' => 'Hello'
        ]);

        $this->transformer = new DealerLogoTransformer();
    }

    public function test_it_can_transform_a_dealer_logo_model()
    {
        $transformedLogo = $this->transformer->transform($this->dealerLogo);
        $this->assertSame(['id', 'dealer_id', 'filename', 'benefit_statement'], array_keys($transformedLogo));
    }

    public function test_it_creates_a_valid_s3_url_for_the_logo()
    {
        $transformedLogo = $this->transformer->transform($this->dealerLogo);

        $url = sprintf('https://%s.s3.amazonaws.com/%s', env('AWS_BUCKET'), $this->dealerLogo->filename);
        $this->assertSame($url, $transformedLogo['filename']);
    }
}
