<?php

namespace App\Services\CRM\Leads\Import;

/**
 * @author David A Conway Jr.
 */
interface ImportServiceInterface {
    /**
     * Takes a lead and export it to the IDS system in XML format
     *
     * @return int total number of imported adf leads
     */
    public function import(): int;
}
