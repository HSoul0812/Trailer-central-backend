<?php

namespace App\Services\Common;

use App\Models\User\User;
use Sentry\Event;

class SentryService
{
    public function beforeSend(Event $event): ?Event
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

        if (auth()->check()) {
            /** @var User $dealer */
            $dealer = auth()->user();
            $dealer->load('website');

            $event->setTags($tags + [
                    'dealer_id' => $dealer->dealer_id,
                    'dealer_name' => $dealer->name,
                    'website_id' => $dealer->website->id,
                    'website_domain' => $dealer->website->domain,
                ]);
        }

        return $event;
    }
}
