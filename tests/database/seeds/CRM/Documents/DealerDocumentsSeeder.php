<?php

namespace Tests\database\seeds\CRM\Documents;

use App\Models\CRM\Documents\DealerDocuments;
use App\Traits\WithGetter;
use Tests\database\seeds\CRM\Leads\AbstractLeadsSeeder;

/**
 * Class DealerDocumentsSeeder
 * @package Tests\database\seeds\CRM\Documents
 *
 * @property-read array $documents
 */
class DealerDocumentsSeeder extends AbstractLeadsSeeder
{
    use WithGetter;

    /**
     * @var bool
     */
    private $withDocuments;

    /**
     * @var array
     */
    private $documents;

    /**
     * InventorySeeder constructor.
     */
    public function __construct($withDocuments = true)
    {
        parent::__construct();
        $this->withDocuments = $withDocuments;
    }

    public function seed(): void
    {
        if (!$this->withDocuments) {
            return;
        }

        $documentsCount = rand(2, 5);

        for ($i = 0; $i < $documentsCount; $i++) {
            $documents = factory(DealerDocuments::class)->create([
                'lead_id' => $this->lead->getKey(),
                'dealer_id' => $this->dealer->getKey(),
            ])->toArray();

            $this->documents[] = $documents;
        }
    }

    public function cleanUp(): void
    {
        DealerDocuments::where('dealer_id', $this->dealer->getKey())->delete();

        parent::cleanUp();
    }
}
