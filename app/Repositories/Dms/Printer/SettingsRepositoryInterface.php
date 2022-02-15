<?php

namespace App\Repositories\Dms\Printer;

use App\Repositories\Repository;
use App\Models\CRM\Dms\Printer\Settings;

interface SettingsRepositoryInterface extends Repository {
    
    /**
     * Gets printer settings by dealerId
     * 
     * @param int $dealerId
     * @throws App\Exceptions\Dms\Printer\PrinterSettingsNotFoundException
     * @return App\Models\CRM\Dms\Printer\Settings
     */
    public function getByDealerId(int $dealerId) : Settings;
    
}
