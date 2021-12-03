<?php

namespace Tests\database\seeds\CRM\Interactions;

use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Interactions\InteractionMessage;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Leads\Lead;
use App\Models\Inventory\Inventory;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * Class InteractionMessageSeeder
 * @package Tests\database\seeds\CRM\Interactions
 *
 * @property-read User $dealer
 * @property-read Lead $lead
 * @property-read EmailHistory $emailHistoryItem
 * @property-read InteractionMessage $emailInteractionMessage
 * @property-read InteractionMessage $textInteractionMessage
 */
class InteractionMessageSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var Lead
     */
    private $lead;

    /**
     * @var EmailHistory
     */
    private $emailHistoryItem;

    /**
     * @var TextLog
     */
    private $textLog;

    /**
     * @var InteractionMessage
     */
    private $emailInteractionMessage;

    /**
     * @var InteractionMessage
     */
    private $textInteractionMessage;

    public function __construct(User $dealer)
    {
        $this->dealer = $dealer;
    }

    public function seed(): void
    {
        $this->lead = factory(Lead::class)->create(['dealer_id' => $this->dealer->dealer_id]);

        $this->emailHistoryItem = factory(EmailHistory::class)->create(['lead_id' => $this->lead->identifier]);

        $this->emailInteractionMessage = InteractionMessage::query()
            ->where(['tb_primary_id' => $this->emailHistoryItem->email_id, 'message_type' => InteractionMessage::MESSAGE_TYPE_EMAIL])
            ->first();

        $this->textLog = factory(TextLog::class)->create(['lead_id' => $this->lead->identifier]);

        $this->textInteractionMessage = InteractionMessage::query()
            ->where(['tb_primary_id' => $this->textLog->id, 'message_type' => InteractionMessage::MESSAGE_TYPE_SMS])
            ->first();
    }

    public function cleanUp(): void
    {
        DealerLocation::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        Website::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        Inventory::where(['dealer_id' => $this->dealer->dealer_id])->delete();
        EmailHistory::where(['lead_id' => $this->lead->identifier])->delete();
        TextLog::where(['lead_id' => $this->lead->identifier])->delete();
        Interaction::where(['tc_lead_id' => $this->lead->identifier])->delete();
        Lead::destroy($this->lead->identifier);
    }
}
