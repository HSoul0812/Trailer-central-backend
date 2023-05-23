<?php

namespace App\Transformers\CRM\Interactions;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Interactions\EmailHistory;
use Carbon\Carbon;

class EmailHistoryTransformer extends TransformerAbstract {

    /**
     * Transform Interaction
     *
     * @param EmailHistory $emailHistory
     * @return array
     */
    public function transform(EmailHistory $emailHistory)
    {
        return [
            'id' => $emailHistory->email_id,
            'interaction_id' => $emailHistory->interaction_id,
            'message_id' => $emailHistory->message_id,
            'to_email' => $emailHistory->to_email,
            'to_name' => $emailHistory->to_name,
            'from_email' => $emailHistory->from_email,
            'from_name' => $emailHistory->from_name,
            'subject' => $emailHistory->subject,
            'body' => utf8_encode($emailHistory->body),
            'is_html' => !empty($emailHistory->use_html) ? true : false,
            'sent' => $emailHistory->date_sent ? Carbon::parse($emailHistory->date_sent) : 'Never',
            'delivered' => $emailHistory->date_delivered ? Carbon::parse($emailHistory->date_delivered) : 'Never',
            'bounced' => $emailHistory->date_bounced ? Carbon::parse($emailHistory->date_bounced) : 'Never',
            'complained' => $emailHistory->date_complained ? Carbon::parse($emailHistory->date_complained) : 'Never',
            'unsubscribed' => $emailHistory->date_unsubscribed ? Carbon::parse($emailHistory->date_unsubscribed) : 'Never',
            'opened' => $emailHistory->date_opened ? Carbon::parse($emailHistory->date_opened) : 'Never',
            'clicked' => $emailHistory->date_clicked ? Carbon::parse($emailHistory->date_clicked) : 'Never',
            'invalid_email' => !empty($emailHistory->invalid_email) ? true : false,
            'was_skipped' => !empty($emailHistory->was_skipped) ? true : false
        ];
    }
}
