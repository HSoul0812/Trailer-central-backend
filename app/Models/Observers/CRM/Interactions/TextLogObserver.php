<?php

namespace App\Models\Observers\CRM\Interactions;

use App\Helpers\SanitizeHelper;
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
     * @var SanitizeHelper
     */
    private $sanitizeHelper;

    /**
     * @param InteractionMessageRepositoryInterface $interactionMessageRepository
     */
    public function __construct(InteractionMessageRepositoryInterface $interactionMessageRepository, SanitizeHelper $sanitizeHelper)
    {
        $this->interactionMessageRepository = $interactionMessageRepository;
        $this->sanitizeHelper = $sanitizeHelper;
    }

    /**
     * @param TextLog $textLog
     * @return void
     */
    public function created(TextLog $textLog)
    {
        $helper = $this->sanitizeHelper;
        $isIncoming = $helper->sanitizePhoneNumber($textLog->from_number) === $helper->sanitizePhoneNumber($textLog->lead->phone_number);

        $this->interactionMessageRepository->create([
            'message_type' => InteractionMessage::MESSAGE_TYPE_SMS,
            'tb_primary_id' => $textLog->id,
            'tb_name' => TextLog::getTableName(),
            'name' => null,
            'hidden' => false,
            'is_read' => !$isIncoming,
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
