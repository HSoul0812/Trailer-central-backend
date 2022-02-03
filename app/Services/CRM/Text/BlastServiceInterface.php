<?php

namespace App\Services\CRM\Text;

use App\Models\User\NewDealerUser;
use App\Models\CRM\Text\Blast;
use Illuminate\Support\Collection;

interface BlastServiceInterface {
    /**
     * Send Blast Text
     * 
     * @param NewDealerUser $dealer
     * @param Blast $blast
     * @throws NoBlastSmsFromNumberException
     * @throws NoLeadsDeliverBlastException
     * @return Collection<BlastSent>
     */
    public function send(NewDealerUser $dealer, Blast $blast): Collection;
}