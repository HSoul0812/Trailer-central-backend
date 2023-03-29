<?php

namespace App\Providers;

use App\Listeners\CRM\Email\SesMessageSentNotification;
use App\Models\Inventory\Image;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryImage;
use App\Models\Website\PaymentCalculator\Settings;
use App\Observers\Inventory\ImageObserver;
use App\Observers\Inventory\InventoryImageObserver;
use App\Observers\Inventory\InventoryObserver;
use App\Observers\Website\PaymentCalculator\SettingsObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class
        ],
        MessageSent::class => [
            SesMessageSentNotification::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Inventory::observe(InventoryObserver::class);
        Settings::observe(SettingsObserver::class);
        InventoryImage::observe(InventoryImageObserver::class);
        Image::observe(ImageObserver::class);
    }
}
