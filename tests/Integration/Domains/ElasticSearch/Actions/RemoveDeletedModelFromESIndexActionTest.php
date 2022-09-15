<?php

namespace Tests\Integration\Domains\ElasticSearch\Actions;

use App\Domains\ElasticSearch\Actions\RemoveDeletedModelFromESIndexAction;
use App\Models\CRM\User\Customer;
use Elasticsearch\Client;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Tests\TestCase;

class RemoveDeletedModelFromESIndexActionTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_ELASTIC_SEARCH
     *
     * @return void
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function testItCanRemoveDeletedCustomerModelsFromESIndex()
    {
        /** @var Client $esClient */
        $esClient = $this->app->make(Client::class);
        $id = 999999999;

        $customer = factory(Customer::class)->make([
            'dealer_id' => 1001,
        ]);

        $createResult = $esClient->create([
            'id' => $id,
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
            ->forModel(Customer::class)
            ->fromDealerId(1001)
            ->withOnDeletedDocumentIdCallback(function (string $deletedDocumentId) use (&$deletedDocumentIds) {
                $deletedDocumentIds[] = $deletedDocumentId;
            })
            ->execute();

        // Make sure that the created document id is one of the deleted documents
        $this->assertTrue(in_array($id, $deletedDocumentIds));

        // Make sure that the total_delete result is not 0
        $this->assertTrue($actionResult['total_delete'] > 0);
    }
}
