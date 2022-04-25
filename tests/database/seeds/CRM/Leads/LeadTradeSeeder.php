<?php

namespace Tests\database\seeds\CRM\Leads;

use App\Models\CRM\Leads\LeadTrade;
use App\Models\CRM\Leads\LeadTradeImage;
use App\Traits\WithGetter;

/**
 * Class LeadTradeSeeder
 * @package Tests\database\seeds\CRM\Leads
 *
 * @property-read array $leadTrades
 */
class LeadTradeSeeder extends AbstractLeadsSeeder
{
    use WithGetter;

    /**
     * @var bool
     */
    private $withTrades;

    /**
     * @var array
     */
    private $leadTrades;

    /**
     * InventorySeeder constructor.
     */
    public function __construct($withDocuments = true)
    {
        parent::__construct();
        $this->withTrades = $withDocuments;
    }

    public function seed(): void
    {
        if (!$this->withTrades) {
            return;
        }

        $tradesCount = rand(2, 5);

        for ($i = 0; $i < $tradesCount; $i++) {
            $leadTrade = factory(LeadTrade::class)->create([
                'lead_id' => $this->lead->getKey()
            ])->toArray();

            $imagesCount = rand(1, 3);

            for ($j = 0; $j < $imagesCount; $j++) {
                $leadTrade['images'][] = factory(LeadTradeImage::class)->create([
                    'trade_id' => $leadTrade['id']
                ])->toArray();
            }

            $this->leadTrades[] = $leadTrade;
        }
    }

    public function cleanUp(): void
    {
        foreach ($this->leadTrades as $leadTrade) {
            LeadTradeImage::where('trade_id', $leadTrade['id'])->delete();
        }

        LeadTrade::where('lead_id', $this->lead->getKey())->delete();

        parent::cleanUp();
    }
}
