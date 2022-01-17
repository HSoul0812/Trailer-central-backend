<?php

namespace Tests\Unit\Domains\ElasticSearch\Actions;

use App\Domains\ElasticSearch\Actions\RemoveDeletedModelFromESIndexAction;
use App\Models\CRM\User\Customer;
use Elasticsearch\Client;
use Illuminate\Support\Str;
use Tests\TestCase;

class RemoveDeletedModelFromESIndexActionTest extends TestCase
{
    /**
     * @covers ::handle
     */
    public function testItCanRemoveDeletedCustomerModelsFromESIndex()
    {
        /** @var Client $esClient */
        $esClient = $this->app->make(Client::class);

        // Use our test dealer id here for simplicity
        $dealerId = 1001;

        $customer = factory(Customer::class)->make();
        $customer->dealer_id = $dealerId;
        $documentId = Str::random();

        $createResult = $esClient->create([
            'id' => $documentId,
            'index' => (new Customer())->searchableAs(),
            'body' => $customer->toArray(),
        ]);

        // Make sure the customer index is created
        $this->assertEquals('created', $createResult['result']);

        /** @var RemoveDeletedModelFromESIndexAction $action */
        $action = $this->app->make(RemoveDeletedModelFromESIndexAction::class);

        // Wait for ES to index the data before execute the action
        sleep(1);

        $deletedDocumentIds = [];

        $actionResult = $action
            ->withModel(Customer::class)
            ->withMustRaw(
                [
                    'match_phrase' => [
                        'dealer_id' => $dealerId
                    ]
                ],
            )
            ->withOnDeletedDocumentIdCallback(function (string $deletedDocumentId) use (&$deletedDocumentIds) {
                $deletedDocumentIds[] = $deletedDocumentId;
            })
            ->execute();

        // Make sure that the created document id is one of the deleted documents
        $this->assertTrue(in_array($documentId, $deletedDocumentIds));

        // Make sure that the total_delete result is not 0
        $this->assertTrue($actionResult['total_delete'] > 0);
    }
}
