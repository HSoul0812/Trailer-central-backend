<?php

namespace App\Services\CRM\User;

use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Repositories\CRM\User\SettingsRepositoryInterface;
use App\Models\CRM\User\Settings;
use App\Models\User\CrmUser;

/**
 * Class SettingsService
 * 
 * @package App\Services\CRM\User
 */
class SettingsService implements SettingsServiceInterface
{
    /**
     * @var CrmUserRepositoryInterface
     */
    private $crmUserRepository;

    /**
     * @var SettingsRepositoryInterface
     */
    private $crmSettingRepository;

    /**
     * 
     * @param CrmUserRepositoryInterface $crmUserRepository
     * @param SettingsRepositoryInterface $crmSettingRepository
     */
    public function __construct(
        CrmUserRepositoryInterface $crmUserRepository,
        SettingsRepositoryInterface $crmSettingRepository
    ) {
        $this->crmUserRepository = $crmUserRepository;
        $this->crmSettingRepository = $crmSettingRepository;
    }

    /**
     * Get All Settings Through CrmUser
     * 
     * @param array $params containing user_id or dealer_id
     * @return CrmUser
     */
    public function getAll(array $params): CrmUser
    {
        return $this->crmUserRepository->get($params);
    }

    /**
     * Update Settings in CrmUser and crm_settings
     * 
     * @param array $params
     * @return array of updated fields
     */
    public function update(array $params): array
    {
        $updatedFields1 = $this->updateCrmUser($params);
        $updatedFields2 = $this->updateCrmSetting($params);

        return $updatedFields1 + $updatedFields2;
    }

    /**
     * Update CRM Settings in CrmUser
     * 
     * @param array $params
     * @return array of updated fields
     */
    protected function updateCrmUser(array $params): array
    {
        // only keep necessary fields
        $settings = array_intersect_key($params, array_flip(CrmUserRepositoryInterface::SETTING_FIELDS));
        $repoParams = $settings;

        if (empty($settings)) return [];

        $repoParams['user_id'] = $params['user_id'];

        $this->crmUserRepository->update($repoParams);

        return $settings;
    }

    /**
     * Update Settings in crm_setting
     * 
     * @param array $params
     * @return array of updated fields
     */
    protected function updateCrmSetting(array $params): array
    {
        // only keep necessary fields
        $settings = array_intersect_key($params, array_flip(SettingsRepositoryInterface::SETTING_FIELDS));
        $repoParams = $settings;

        if (empty($settings)) return [];
        
        $repoParams['user_id'] = $params['user_id'];

        $this->crmSettingRepository->update($repoParams);

        return $settings;
    }
}