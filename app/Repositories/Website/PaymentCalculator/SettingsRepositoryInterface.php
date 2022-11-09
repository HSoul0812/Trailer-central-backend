<?php

namespace App\Repositories\Website\PaymentCalculator;

use App\Repositories\Repository;

interface SettingsRepositoryInterface extends Repository {
    public function getCalculatedSettings(array $params): array;
}
