<?php

namespace App\Models\Observers\CRM\Interactions;

use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Interactions\InteractionMessage;
use App\Repositories\CRM\Interactions\InteractionMessageRepositoryInterface;

/**
 * Class EmailHistoryObserver
 * @package App\Models\Observers\CRM\Interactions
 */
class EmailHistoryObserver
{
    /**
     * @var InteractionMessageRepositoryInterface
     */
    private $interactionMessageRepository;

    /**
     * @param InteractionMessageRepositoryInterface $interactionMessageRepository
     */
    public function __construct(InteractionMessageRepositoryInterface $interactionMessageRepository)
    {
        $this->interactionMessageRepository = $interactionMessageRepository;
    }

    /**
     * @param EmailHistory $emailHistory
     * @return void
     */
    public function created(EmailHistory $emailHistory)
    {
        if (empty($emailHistory->lead_id) || empty($emailHistory->date_sent)) {
            return;
        }

        $isIncoming = strcasecmp($emailHistory->lead->email_address, $emailHistory->from_email) === 0;

        $this->interactionMessageRepository->create([
            'message_type' => InteractionMessage::MESSAGE_TYPE_EMAIL,
            'tb_primary_id' => $emailHistory->email_id,
            'tb_name' => EmailHistory::getTableName(),
            'name' => null,
            'hidden' => false,
            'is_read' => !$isIncoming,
        ]);
    }

    /**
     * @param EmailHistory $emailHistory
     * @return void
     */
    public function updated(EmailHistory $emailHistory)
    {
        $params = [
            'tb_primary_id' => $emailHistory->email_id,
            'tb_name' => EmailHistory::getTableName(),
        ];

        $interactionMessage = $this->interactionMessageRepository->get($params);

        if ($interactionMessage) {
            $this->interactionMessageRepository->searchable($params);
        } else {
            $this->created($emailHistory);
        }
    }

    /**
     * @param EmailHistory $emailHistory
     * @return void
     */
    public function deleted(EmailHistory $emailHistory)
    {
        $this->interactionMessageRepository->delete([
            'tb_primary_id' => $emailHistory->email_id,
            'tb_name' => EmailHistory::getTableName(),
        ]);
    }
}
