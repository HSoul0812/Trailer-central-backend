<?php

namespace App\Services\CRM\Text;

use App\Models\CRM\Interactions\TextLog;

/**
 * Interface TextServiceInterface
 * @package App\Services\CRM\Text
 */
interface TextServiceInterface
{
    /**
     * Send Text
     *
     * @param int $leadId
     * @param string $textMessage
     * @param array $mediaUrl
     * @return TextLog
     */
    public function send(int $leadId, string $textMessage, array $mediaUrl = []): TextLog;

    /**
     * @param array $params
     * @return bool
     */
    public function reply(array $params): bool;
}
