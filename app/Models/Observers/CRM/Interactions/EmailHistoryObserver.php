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
        $this->interactionMessageRepository->create([
            'message_type' => InteractionMessage::MESSAGE_TYPE_EMAIL,
            'tb_primary_id' => $emailHistory->email_id,
            'tb_name' => EmailHistory::getTableName(),
            'name' => null,
            'hidden' => false
        ]);
    }

    /**
     * @param EmailHistory $textLog
     * @return void
     */
    public function updated(EmailHistory $textLog)
    {
        $this->interactionMessageRepository->searchable([
            'tb_primary_id' => $textLog->email_id,
            'tb_name' => EmailHistory::getTableName(),
        ]);
    }

    /**
     * @param EmailHistory $textLog
     * @return void
     */
    public function deleted(EmailHistory $textLog)
    {
        $this->interactionMessageRepository->delete([
            'tb_primary_id' => $textLog->email_id,
            'tb_name' => EmailHistory::getTableName(),
        ]);
    }
}
