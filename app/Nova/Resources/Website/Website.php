<?php

namespace App\Nova\Resources\Website;

use App\Nova\Metrics\DealerWebsitesUptime;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Select;
use App\Models\Website\Config\WebsiteConfig;
use App\Nova\Actions\Website\ChangeOEMStatus;
use App\Models\Website\Website as DealerWebsite;

use Laravel\Nova\Panel;
use Laravel\Nova\Fields\DateTime;
use Spatie\SslCertificate\SslCertificate;

use App\Nova\Resource;
use Laravel\Nova\Http\Requests\NovaRequest;

use App\Nova\Actions\Dealer\Subscriptions\Google\IssueCertificateSSL;
use App\Nova\Actions\Dealer\Subscriptions\Google\EnableProxiedDomainsSsl;

class Website extends Resource
{
    public static $group = 'Websites';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Website\Website';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'domain';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'domain',
        'type',
        'dealer_id'
    ];

    const INVENTORY_SOURCE_MAP = [
        "env" => 'ENV Based',
        'es' => 'Old Elastic Way',
        'sdk' => 'SDK',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $model = $this->model();
        if (!empty($model)) {
            $configs = $model->websiteConfigs()->get();
            $sourceConfig = $configs->filter(function (WebsiteConfig $config) {
                return $config->key == 'inventory/source';
            })->first();
        }

        return [
            Text::make('Website ID', 'id')->exceptOnForms(),

            Text::make('Dealer ID', 'dealer_id')->sortable(),

            Text::make('App ID', 'identifier')->exceptOnForms(),

            new Panel('Domain', [
                Boolean::make('Certified', function () {
                    return $this->ssl_certificate ? $this->ssl_certificate->isValid() : null;
                })->hideFromIndex(),

                Text::make('Domain')
                    ->sortable(),

                Text::make('Issuer', function () {
                    return $this->ssl_certificate ? $this->ssl_certificate->getIssuer() : null;
                })->hideFromIndex(),

                DateTime::make('Valid From', function () {
                    return $this->ssl_certificate ? $this->ssl_certificate->validFromDate() : null;
                })->hideFromIndex()->format('DD MMM, YYYY - LT'),

                DateTime::make('Expiration Date', function () {
                    return $this->ssl_certificate ? $this->ssl_certificate->expirationDate() : null;
                })->hideFromIndex()->format('DD MMM, YYYY - LT')
            ]),

            Select::make('Type', 'type')
                ->options($this->websiteTypes())
                ->displayUsingLabels(),

            Text::make('Template')->help("This will apply as the CertificateName on SSL certificates")->hideFromIndex(),

            Boolean::make('OEM', 'is_oem')->sortable(),

            Boolean::make('Active', 'is_active')->sortable(),

            Boolean::make('Responsive', 'responsive')->sortable(),

            Select::make('Inventory Source', 'inventory_source')->withMeta(['value' => $sourceConfig->value ?? 'env' ])->options(self::INVENTORY_SOURCE_MAP),

            Textarea::make('Global Filter', 'type_config')->sortable()->help(
              "Usage:<br>
              {field}|{operator}|{value} - Each in a new line<br>

              Example Usage:<br>
              manufacturer|eq|Stealth Trailers<br>
              condition|eq|used<br>
              stalls|gte|3<br>

              Allowed Fields:<br>
              dealer_id<br>
              manufacturer<br>
              condition<br>
              category<br>
              dealer_location_id<br>
              year<br>
              status<br>
              is_special<br>
              is_featured<br>
              axles<br>
              construction<br>
              pull_type<br>
              ramps<br>
              livingquarters<br>
              stalls<br>
              configuration<br>
              midtack<br>
              roof_type<br>
              nose_type<br>
              color<br>
              sleeping_capacity<br>
              air_conditioners<br>
              fuel_type<br>
              is_rental<br>
              mileage<br>
              slideouts<br>
              manger<br>
              shortwall_length<br>
              number_batteries<br>
              horsepower<br>
              tires<br>
              passengers<br>
              model<br>
              title<br>
              description<br>
              stock<br>
              created_at<br>
              updated_at<br>

              Allowed Operators:<br>
              eq (is equal)<br>
              neq (is not equal)<br>
              lt (is less than)<br>
              gt (is greater than)<br>
              gte (is greater than or equal)<br>
              lte (is less than or equal)<br>"
            ),

            Textarea::make('Head Scripts', 'HeadScripts')->hideFromIndex(),

        ];
    }

    public function websiteTypes(): array
    {
        return [
            DealerWebsite::WEBSITE_TYPE_CUSTOM => 'Custom',
            DealerWebsite::WEBSITE_TYPE_WEBSITE => 'Website',
            DealerWebsite::WEBSITE_TYPE_CLASSIFIED => 'Classified'
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            (new DealerWebsitesUptime())->width('1/6')
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [

        ];
    }

    public static function fillForUpdate(NovaRequest $request, $model)
    {
        if ($request->input('inventory_source')) {

            /** @var \App\Models\Website\Website $website */
            $website = $model;

            $has_config = false;
            foreach ( $website->websiteConfigs()->get() as $config ) {
                if ($config->key == 'inventory/source') {
                    $config->value = $request->input('inventory_source');
                    $config->save();
                    $has_config = true;
                }
            }

            if ( $has_config == false ) {
                $new_conf = new WebsiteConfig();
                $new_conf->website_id = $website->id;
                $new_conf->key = 'inventory/source';
                $new_conf->value = $request->input('inventory_source');
                $new_conf->save();
            }

            $request->offsetUnset('inventory_source');
        }

        return parent::fillForUpdate($request, $model);
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            app()->make(IssueCertificateSsl::class),
            app()->make(EnableProxiedDomainsSsl::class),
            app()->make(ChangeOEMStatus::class)
        ];
    }
}
