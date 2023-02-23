<?php

namespace App\Http;

use App\Http\Middleware\CRM\Text\ReplyTextValidate;
use App\Http\Middleware\Ecommerce\StripeWebhookValidate;
use App\Http\Middleware\Ecommerce\TexTrailWebhookValidate;
use App\Http\Middleware\Ecommerce\ValidHookIpMiddleware;
use App\Http\Middleware\Inventory\CreateInventoryPermissionMiddleware;
use App\Http\Middleware\Inventory\InvalidatePermissionMiddleware;
use App\Http\Middleware\User\ManageAccountPermissionMiddleware;
use App\Http\Middleware\SetDealerIdFilterOnRequest;
use App\Http\Middleware\SetDealerIdWhenAuthenticatedOnRequest;
use App\Http\Middleware\ValidateDealerIdOnRequest;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\AccessToken;
use App\Http\Middleware\Website\FieldMapValidate;
use App\Http\Middleware\SetDealerIdOnRequest;
use App\Http\Middleware\SetWebsiteIdOnRequest;
use App\Http\Middleware\SetUserIdOnRequest;
use App\Http\Middleware\SetSalesPersonIdOnRequest;
use App\Http\Middleware\ValidAccessToken;
use App\Http\Middleware\CRM\Interactions\InteractionValidate;
use App\Http\Middleware\CRM\Interactions\Facebook\MessageValidate;
use App\Http\Middleware\CRM\Email\TemplateValidate as EmailTemplateValidate;
use App\Http\Middleware\CRM\Email\BlastValidate as EmailBlastValidate;
use App\Http\Middleware\CRM\Email\CampaignValidate as EmailCampaignValidate;
use App\Http\Middleware\CRM\Text\TextValidate;
use App\Http\Middleware\CRM\Text\TemplateValidate as TextTemplateValidate;
use App\Http\Middleware\CRM\Text\BlastValidate as TextBlastValidate;
use App\Http\Middleware\CRM\Text\CampaignValidate as TextCampaignValidate;
use App\Http\Middleware\CRM\User\SalesPersonValidate;
use App\Http\Middleware\Dispatch\FacebookValidate;
use App\Http\Middleware\Dms\Printer\FormValidate as PrinterFormValidate;
use App\Http\Middleware\Dms\Printer\InstructionValidate as PrinterInstructionValidate;
use App\Http\Middleware\Integration\AuthValidate;
use App\Http\Middleware\Integration\Facebook\CatalogValidate;
use App\Http\Middleware\Integration\Facebook\ChatValidate;
use App\Http\Middleware\CRM\Leads\LeadTradeValidate;
use App\Http\Middleware\Marketing\Facebook\MarketplaceValidate;
use App\Http\Middleware\Marketing\Facebook\PagetabValidate;
use App\Http\Middleware\Parts\PartOrderValidate;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        AccessToken::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class
        ],

        'api' => [
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            CorsMiddleware::class
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'cors' => CorsMiddleware::class,
        'forms.field-map.validate' => FieldMapValidate::class,
        'accesstoken.validate' => ValidAccessToken::class,
        'setDealerIdOnRequest' => SetDealerIdOnRequest::class,
        'setDealerIdWhenAuthenticatedOnRequest' => SetDealerIdWhenAuthenticatedOnRequest::class,
        'setDealerIdFilterOnRequest' => SetDealerIdFilterOnRequest::class,
        'setWebsiteIdOnRequest' => SetWebsiteIdOnRequest::class,
        'setUserIdOnRequest' => SetUserIdOnRequest::class,
        'setSalesPersonIdOnRequest' => SetSalesPersonIdOnRequest::class,
        'interaction.validate' => InteractionValidate::class,
        'emailbuilder.template.validate' => EmailTemplateValidate::class,
        'emailbuilder.campaign.validate' => EmailCampaignValidate::class,
        'emailbuilder.blast.validate' => EmailBlastValidate::class,
        'text.validate' => TextValidate::class,
        'text.template.validate' => TextTemplateValidate::class,
        'text.campaign.validate' => TextCampaignValidate::class,
        'text.blast.validate' => TextBlastValidate::class,
        'integration.auth.validate' => AuthValidate::class,
        'facebook.catalog.validate' => CatalogValidate::class,
        'facebook.chat.validate' => ChatValidate::class,
        'facebook.message.validate' => MessageValidate::class,
        'sales-person.validate' => SalesPersonValidate::class,
        'parts.orders.validate' => PartOrderValidate::class,
        'printer.form.validate' => PrinterFormValidate::class,
        'printer.instruction.validate' => PrinterInstructionValidate::class,
        'inventory.create.permission' => CreateInventoryPermissionMiddleware::class,
        'accounts.manage.permission' => ManageAccountPermissionMiddleware::class,
        'stripe.webhook.validate' => StripeWebhookValidate::class,
        'textrail.webhook.validate' => TexTrailWebhookValidate::class,
        'marketing.facebook.marketplace' => MarketplaceValidate::class,
        'marketing.facebook.pagetab' => PagetabValidate::class,
        'dispatch.facebook' => FacebookValidate::class,
        'replytext.validate' => ReplyTextValidate::class,
        'validateDealerIdOnRequest' => ValidateDealerIdOnRequest::class,
        'inventory.cache.permission' => InvalidatePermissionMiddleware::class,
        'leads.trade.validate' => LeadTradeValidate::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
