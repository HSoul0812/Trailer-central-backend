<?php

namespace App\Repositories\Dms\Printer;

use App\Repositories\Dms\Printer\SettingsRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Printer\Settings;
use App\Models\User\User;
use App\Exceptions\Dms\Printer\PrinterSettingsNotFoundException;

class SettingsRepository implements SettingsRepositoryInterface {
    
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }
    
    public function getByDealerId(int $dealerId) : Settings
    {
        $user = User::findOrFail($dealerId);
        if ( $user->printerSettings ) {
            return $user->printerSettings;
        }        
        
        throw new PrinterSettingsNotFoundException;
    }

}
