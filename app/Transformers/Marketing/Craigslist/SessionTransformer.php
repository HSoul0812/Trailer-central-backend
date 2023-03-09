<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Session;
use League\Fractal\TransformerAbstract;

/**
 * Class SessionTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class SessionTransformer extends TransformerAbstract
{
    /**
     * @param Session $session
     * @return array
     */
    public function transform(Session $session): array
    {
        return [
            'id' => $session->session_row_id,
            'session_id' => $session->session_id,
            'dealer_id' => $session->session_dealer_id,
            'profile_id' => $session->session_profile_id,
            'slot_id' => $session->session_slot_id,
            'client_id' => $session->session_client,
            'scheduled_at' => $session->session_scheduled,
            'started_at' => $session->session_started,
            'confirmed_at' => $session->session_confirmed,
            'last_session_at' => $session->session_last_activity,
            'last_webui_at' => $session->webui_last_activity,
            'last_dispatch_at' => $session->dispatch_last_activity,
            'last_item_began_at' => $session->last_item_began,
            'sound_notify' => $session->sound_notify,
            'recoverable' => $session->recoverable,
            'status' => $session->status,
            'state' => $session->state,
            'text_status' => $session->text_status,
            'nooped' => $session->nooped,
            'nooped_until' => $session->nooped_until,
            'queue_length' => $session->queue_length,
            'log' => $session->log,
            'market_code' => $session->market_code,
            'prev_url' => $session->prev_url,
            'prev_url_skip' => $session->prev_url_skip,
            'sync_page_count' => $session->sync_page_count,
            'sync_current_page' => $session->sync_current_page,
            'ajax_url' => $session->ajax_url,
            'notify_error_init' => $session->notify_error_init,
            'notify_error_timeout' => $session->notify_error_timeout,
            'dismissed' => $session->dismissed,
            'tz_offset' => $session->tz_offset
        ];
    }
}