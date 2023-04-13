<?php

namespace App\Services\CRM\User;

use App\Models\User\CrmUser;

interface SettingsServiceInterface {

    /**
     * Get All Settings
     * 
     * @param array $params
     * @return CrmUser
     */
    public function getAll(array $params): CrmUser;

    /**
     * Update User Settings
     * 
     * @param array $params
     * @return array
     */
    public function update(array $params): array;
}