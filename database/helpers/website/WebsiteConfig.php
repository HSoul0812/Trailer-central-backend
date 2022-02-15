<?php

declare(strict_types=1);

namespace Database\helpers\website;

use Illuminate\Support\Facades\DB;

class WebsiteConfig
{
    /**
     * @param string $dealerName
     * @param string $keyName
     * @param string|int $configValue
     * @return void
     */
    public static function setKeyValueByDealerName(string $dealerName, string $keyName, $configValue): void
    {
        $websiteId = self::getWebsiteIdByDealerName($dealerName);

        if ($websiteId) {
            $websiteConfig = [
                'website_id' => $websiteId,
                'key' => $keyName,
            ];

            DB::table('website_config')->updateOrInsert(
                $websiteConfig,
                $websiteConfig + ['value' => $configValue]
            );
        }
    }

    public static function getWebsiteIdByDealerName(string $dealerName): ?int
    {
        $website = DB::table('website')
            ->select('id')
            ->join('dealer', 'dealer.dealer_id', '=', 'website.dealer_id')
            ->where('dealer.name', $dealerName)
            ->first('id');

        return $website ? $website->id : null;
    }
}
