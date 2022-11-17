<?php

namespace App\Services\Common;

use App\Models\User\NovaUser;
use App\Models\User\User;
use Sentry\Event;

class SentryService
{
    public static function beforeSend(Event $event): ?Event
    {
        $tags = [];

        $isDealerSitesProject = collect([
            'ecommerce',
            'inventory',
            'textrail',
            'website'
        ])->contains(function ($route) {
            return request()->is("api/$route/*");
        });

        if ($isDealerSitesProject) {
            $tags['project'] = 'dealer-sites';
        }

        $dealer = auth()->user();

        if (isset($dealer)) {
            if ($dealer instanceof NovaUser) {
                return $event;
            }

            $dealer->load('website');

            $tags = array_merge($tags, [
                'dealer_id' => $dealer->dealer_id,
                'dealer_name' => $dealer->name,
                'website_id' => (!empty($dealer->website)) ? $dealer->website->id : 0,
                'website_domain' => (!empty($dealer->website)) ? $dealer->website->domain : '',
            ]);
        }

        $event->setTags($tags);
        return $event;
    }
}
