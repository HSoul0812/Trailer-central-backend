<?php

namespace App\Services\Dms\Quote;

interface QuoteSettingServiceInterface
{
    public function update(array $params, int $dealerId);
}
