<?php

namespace Tests\Unit\Transformers\User;

use App\Models\User\DealerLogo;
use App\Services\User\DealerLogoService;
use App\Transformers\User\DealerLogoTransformer;
use Illuminate\Support\Facades\Storage;
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

        $url = Storage::disk(DealerLogoService::STORAGE_DISK)->url($this->dealerLogo->filename);
        $this->assertSame($url, $transformedLogo['filename']);
    }
}
