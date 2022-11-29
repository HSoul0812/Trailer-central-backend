<?php

namespace Tests\Integration\Domains\ElasticSearch\Actions;

use App\Domains\Parts\Actions\IndexDealerPartsToESAction;
use App\Models\Parts\Part;
use App\Models\User\User;
use Elasticsearch\Client;
use Tests\TestCase;

class IndexDealerPartsToESActionTest extends TestCase
{
    /**
     * @var Client
     */
    private $esClient;

    protected function setUp(): void
    {
        $this->esClient = resolve(Client::class);
    }

    /**
     * We test the action by creating a new dealer and a part within it
     * the action must be able to index that part into ES and if it is
     * we'll consider it a success
     *
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testItCanIndexAllDealerPartsToES()
    {
        /** @var User $dealer */
        $dealer = factory(User::class)->create();

        $numberOfPartToCreate = 7;

        factory(Part::class, $numberOfPartToCreate)->create([
            'dealer_id' => $dealer->dealer_id,
        ]);

        $esParts = $this->searchForPartInES($dealer->dealer_id);

        $this->assertEquals(0, data_get($esParts, 'hits.total.value'));

        resolve(IndexDealerPartsToESAction::class)->execute($dealer);

        // Give it two seconds for the data to be indexed to ES
        sleep(3);

        $esParts = $this->searchForPartInES($dealer->dealer_id);

        $this->assertEquals($numberOfPartToCreate, data_get($esParts, 'hits.total.value'));
    }

    /**
     * @param int $dealerId
     * @return array
     */
    private function searchForPartInES(int $dealerId): array
    {
        return $this->esClient->search([
            'index' => (new Part())->searchableAs(),
            'body' => [
                'query' => [
                    'match' => [
                        'dealer_id' => (string) $dealerId,
                    ],
                ],
            ],
        ]);
    }
}
