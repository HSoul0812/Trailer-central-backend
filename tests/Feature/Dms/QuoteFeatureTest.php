<?php

namespace Tests\Feature\Dms;

use App\Models\CRM\Dms\UnitSale;
use Tests\TestCase;

/**
 * Class QuoteFeatureTest
 *
 * @package Tests\Feature\Dms
 */
class QuoteFeatureTest extends TestCase
{
    private const API_QUOTES_ARCHIVE = '/api/user/quotes';

    private const API_QUOTES_BULK_ARCHIVE = '/api/user/quotes/bulk-archive';

    private const INVALID_ENTITY_MESSAGE = [
        'message' => 'The given data was invalid.',
    ];

    // use a known sample ID from the database with valid values
    protected $dealerId = 1001;

    /**
     * @group DMS
     * @group DMS_QUOTE
     *
     * @return void
     */
    public function testListHttpOk()
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get(self::API_QUOTES_ARCHIVE);

        $response->assertStatus(200);
    }

    /**
     * @group DMS
     * @group DMS_QUOTE
     *
     * @return void
     */
    public function testBulkUpdateMissingAccessToken()
    {
        $response = $this
            ->put(self::API_QUOTES_BULK_ARCHIVE);

        $response->assertStatus(403);
    }

    /**
     * @group DMS
     * @group DMS_QUOTE
     *
     * @return void
     */
    public function testBulkUpdateMissingRequiredData()
    {
        $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->put(self::API_QUOTES_BULK_ARCHIVE)
            ->assertStatus(422)
            ->assertJsonFragment(
                $this->generateQuoteIdsValidationMessage('The quote ids field is required.')
            );
    }

    /**
     * @group DMS
     * @group DMS_QUOTE
     *
     * @return void
     */
    public function testBulkUpdateInvalidDataType()
    {
        $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->put(self::API_QUOTES_BULK_ARCHIVE, [
                'quote_ids' => 12345,
            ])
            ->assertStatus(422)
            ->assertJsonFragment(
                $this->generateQuoteIdsValidationMessage('The quote ids needs to be an array.')
            );
    }

    public function generateQuoteIdsValidationMessage(string $message)
    {
        return $this->generateValidationErrors([
            'quote_ids' => [
                $message,
            ],
        ]);
    }

    /**
     * @group DMS
     * @group DMS_QUOTE
     *
     * @return void
     */
    public function testBulkUpdateValidData()
    {
        $quote = $this->makeQuotes();
        $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->put(self::API_QUOTES_BULK_ARCHIVE, [
                'quote_ids' => [
                    $quote->getKey(),
                ],
            ])
            ->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'success',
            ]);

        $this->assertDatabaseHas('dms_unit_sale', [
            'id' => $quote->getKey(),
            'is_archived' => true,
        ]);

        $quote->forceDelete();
    }

    public function makeQuotes()
    {
        $model = new UnitSale();
        $model->dealer_id = $this->dealerId;
        $model->is_archived = false;

        $model->save();

        return $model;
    }

    /**
     * @param mixed ...$errors
     *
     * @return array
     */
    protected function generateValidationErrors(...$errors): array
    {
        return [
            'errors' => array_merge_recursive(...$errors),
        ] + self::INVALID_ENTITY_MESSAGE;
    }
}
