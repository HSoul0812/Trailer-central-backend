<?php

namespace App\Providers;

use App\Models\CRM\Dealer\DealerFBMOverview;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Models\CRM\Leads\Jotform\WebsiteForms;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadAssign;
use App\Models\FeatureFlag;
use App\Models\Feed\Factory\ShowroomGenericMap;
use App\Models\Feed\Feed;
use App\Models\Feed\Mapping\ExternalDealerMapping;
use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping;
use App\Models\Feed\Mapping\Incoming\DealerIncomingPendingMapping;
use App\Models\Feed\TransactionExecuteQueue;
use App\Models\Integration\Collector\Collector;
use App\Models\Integration\Collector\CollectorChangeReport;
use App\Models\Integration\Collector\CollectorFields;
use App\Models\Integration\Collector\CollectorLog;
use App\Models\Integration\Collector\CollectorSpecification;
use App\Models\Integration\Collector\CollectorSpecificationAction;
use App\Models\Integration\Collector\CollectorSpecificationRule;
use App\Models\Integration\Integration;
use App\Models\Inventory\Category;
use App\Models\Inventory\EntityType;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryMfg;
use App\Models\Inventory\Manufacturers\Brand;
use App\Models\Inventory\Manufacturers\Manufacturers;
use App\Models\Marketing\Craigslist\Balance;
use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Parts\Type;
use App\Models\Parts\Vendor;
use App\Models\User\DealerLocation;
use App\Models\User\NovaUser;
use App\Models\User\User;
use App\Models\Website\Entity;
use App\Models\Website\Forms\FieldMap;
use App\Models\Website\Website;
use App\Nova\Policies\ApiEntityReferencePolicy;
use App\Nova\Policies\BalancePolicy;
use App\Nova\Policies\CollectorChangeReportPolicy;
use App\Nova\Policies\CollectorFieldPolicy;
use App\Nova\Policies\CollectorLogPolicy;
use App\Nova\Policies\CollectorPolicy;
use App\Nova\Policies\CollectorSpecificationActionPolicy;
use App\Nova\Policies\CollectorSpecificationPolicy;
use App\Nova\Policies\CollectorSpecificationRulePolicy;
use App\Nova\Policies\DealerPolicy;
use App\Nova\Policies\FeatureFlagPolicy;
use App\Nova\Policies\ExternalDealerMappingPolicy;
use App\Nova\Policies\FeedPolicy;
use App\Nova\Policies\FieldMapPolicy;
use App\Nova\Policies\IntegrationPolicy;
use App\Nova\Policies\InventoryPolicy;
use App\Nova\Policies\JotformPolicy;
use App\Nova\Policies\LeadAssignPolicy;
use App\Nova\Policies\DealerIncomingPendingMappingPolicy;
use App\Nova\Policies\DealerFBPolicy;
use App\Nova\Policies\CategoryPolicy;
use App\Nova\Policies\EntityTypePolicy;
use App\Nova\Policies\BrandPolicy;
use App\Nova\Policies\LeadPolicy;
use App\Nova\Policies\LocationPolicy;
use App\Nova\Policies\DealerIncomingMappingPolicy;

use App\Nova\Policies\InventoryMfgPolicy;
use App\Nova\Policies\ManufacturersPolicy;
use App\Nova\Policies\MarketplacePolicy;
use App\Nova\Policies\PartBrandPolicy;
use App\Nova\Policies\PartCategoryPolicy;
use App\Nova\Policies\PartTypePolicy;
use App\Nova\Policies\PartVendorPolicy;
use App\Nova\Policies\PermissionPolicy;
use App\Nova\Policies\QuickbookApprovalPolicy;
use App\Nova\Policies\RolePolicy;
use App\Nova\Policies\ShowroomGenericMapPolicy;
use App\Nova\Policies\TransactionExecuteQueuePolicy;
use App\Nova\Policies\UserPolicy;
use App\Nova\Policies\WebsiteEntityPolicy;
use App\Nova\Policies\WebsitePolicy;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Balance::class => BalancePolicy::class,
        DealerFBMOverview::class => DealerFBPolicy::class,
        User::class => DealerPolicy::class,
        DealerLocation::class => LocationPolicy::class,
        ApiEntityReference::class => ApiEntityReferencePolicy::class,
        Collector::class => CollectorPolicy::class,
        Inventory::class => InventoryPolicy::class,
        Category::class => CategoryPolicy::class,
        InventoryMfg::class => InventoryMfgPolicy::class,
        EntityType::class => EntityTypePolicy::class,
        DealerIncomingPendingMapping::class => DealerIncomingPendingMappingPolicy::class,
        DealerIncomingMapping::class => DealerIncomingMappingPolicy::class,
        WebsiteForms::class => JotformPolicy::class,
        LeadAssign::class => LeadAssignPolicy::class,
        Lead::class => LeadPolicy::class,
        Brand::class => BrandPolicy::class,
        Manufacturers::class => ManufacturersPolicy::class,
        Feed::class => FeedPolicy::class,
        FieldMap::class => FieldMapPolicy::class,
        NovaUser::class => UserPolicy::class,
        \App\Models\Parts\Brand::class => PartBrandPolicy::class,
        \App\Models\Parts\Category::class => PartCategoryPolicy::class,
        Type::class => PartTypePolicy::class,
        Vendor::class => PartVendorPolicy::class,
        QuickbookApproval::class => QuickbookApprovalPolicy::class,
        ShowroomGenericMap::class => ShowroomGenericMapPolicy::class,
        Website::class => WebsitePolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        CollectorChangeReport::class => CollectorChangeReportPolicy::class,
        CollectorLog::class => CollectorLogPolicy::class,
        TransactionExecuteQueue::class => TransactionExecuteQueuePolicy::class,
        Entity::class => WebsiteEntityPolicy::class,
        Marketplace::class => MarketplacePolicy::class,
        Integration::class => IntegrationPolicy::class,
        ExternalDealerMapping::class => ExternalDealerMappingPolicy::class,
        CollectorFields::class => CollectorFieldPolicy::class,
        CollectorSpecification::class => CollectorSpecificationPolicy::class,
        CollectorSpecificationRule::class => CollectorSpecificationRulePolicy::class,
        CollectorSpecificationAction::class => CollectorSpecificationActionPolicy::class,
        FeatureFlag::class => FeatureFlagPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        $this->registerPolicies();

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->input('api_token')) {
                return User::where('api_token', $request->input('api_token'))->first();
            }
        });
    }
}
