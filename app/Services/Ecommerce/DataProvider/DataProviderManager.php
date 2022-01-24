<?php
namespace App\Services\Ecommerce\DataProvider;

use App\Services\Ecommerce\DataProvider\Providers\TextrailMagento;

class DataProviderManager implements DataProviderManagerInterface
{
    public function getProvider(): DataProviderInterface
    {
        $provider = null;
        $active_provider = env("ECOMMERCE_DATA_PROVIDER", "textrail");

        switch ($active_provider) {
            case "textrail":
                $provider = new TextrailMagento();
                break;
        }

        return $provider;
    }
}