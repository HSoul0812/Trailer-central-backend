<?php

namespace App\Models\Observers\CRM\Interactions;

use App\Models\CRM\Interactions\InteractionMessage;
use App\Models\CRM\Interactions\TextLog;
use App\Repositories\CRM\Interactions\InteractionMessageRepositoryInterface;

/**
 * Class TextLogObserver
 * @package App\Models\Observers\CRM\Interactions
 */
class TextLogObserver
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
     * @param TextLog $textLog
     * @return void
     */
    public function created(TextLog $textLog)
    {
        $this->interactionMessageRepository->create([
            'message_type' => InteractionMessage::MESSAGE_TYPE_SMS,
            'tb_primary_id' => $textLog->id,
            'tb_name' => TextLog::getTableName(),
            'name' => null,
            'hidden' => false
        ]);
    }

    /**
     * @param TextLog $textLog
     * @return void
     */
    public function updated(TextLog $textLog)
    {
        $this->interactionMessageRepository->searchable([
            'tb_primary_id' => $textLog->id,
            'tb_name' => TextLog::getTableName(),
        ]);
    }

    /**
     * @param TextLog $textLog
     * @return void
     */
    public function deleted(TextLog $textLog)
    {
        $this->interactionMessageRepository->delete([
            'tb_primary_id' => $textLog->id,
            'tb_name' => TextLog::getTableName(),
        ]);
    }
}
