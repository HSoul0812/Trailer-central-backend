<?php

namespace App\Transformers\CRM\Email;

use App\Services\CRM\Email\DTOs\ImapMailbox;
use League\Fractal\TransformerAbstract;

class ImapMailboxTransformer extends TransformerAbstract
{
    /**
     * Transform ImapMailbox
     * 
     * @param ImapMailbox $mailbox
     * @return array
     */
    public function transform(ImapMailbox $mailbox)
    {
        return [
            'full' => $mailbox->full,
            'attributes' => (int) $mailbox->attributes,
            'delimiter' => $mailbox->delimiter,
            'name' => $mailbox->name
        ];
    }
}