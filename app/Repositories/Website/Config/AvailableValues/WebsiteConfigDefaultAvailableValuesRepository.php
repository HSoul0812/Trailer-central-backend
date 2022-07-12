<?php

declare(strict_types=1);

namespace App\Repositories\Website\Config\AvailableValues;

use App\Models\Website\Config\WebsiteConfigDefault;
use App;

class WebsiteConfigDefaultAvailableValuesRepository implements WebsiteConfigDefaultAvailableValuesRepositoryInterface
{
    private const VARIABLES_WITH_CUSTOM_AVAILABLE_VALUES = [
        'inventory/filters/enable_filters' => EnabledFiltersRepository::class,
        'showroom/brands' => ShowRoomRepository::class
    ];

    /**
     * It will use a custom repository to pull the available values for a particular website variable, if there is not
     * a custom repository, then it will pull the default available values right from the website default variable
     *
     * @param WebsiteConfigDefault $config
     * @param int $websiteId
     * @return mixed
     */
    public function getCustomAvailableValuesFor(WebsiteConfigDefault $config, int $websiteId)
    {
        if (!$config->exists) {
            throw new \RuntimeException('`WebsiteConfigDefaultAvailableValues::getCustomAvailableValues` There is not a loaded record');
        }

        if ($this->hasCustomAvailableValues($config->key)) {
            /** @var AvailableValuesRepositoryInterface $customAvailableValueRepository */
            $customAvailableValueRepository = App::make(self::VARIABLES_WITH_CUSTOM_AVAILABLE_VALUES[$config->key]);

            return $customAvailableValueRepository->pull($websiteId);
        }

        return $config->values;
    }

    private function hasCustomAvailableValues(string $key): bool
    {
        return array_key_exists($key, self::VARIABLES_WITH_CUSTOM_AVAILABLE_VALUES);
    }
}
