<?php

namespace App\Nova\Resources\Website;

use App\Nova\Actions\Dealer\Subscriptions\Google\EnableProxiedDomainsSsl;
use App\Nova\Actions\Dealer\Subscriptions\Google\IssueCertificateSSL;
use App\Nova\Metrics\DealerWebsitesUptime;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Boolean;
use App\Models\Website\Website as DealerWebsite;
use Laravel\Nova\Fields\Select;

use Laravel\Nova\Panel;
use Laravel\Nova\Fields\DateTime;
use Spatie\SslCertificate\SslCertificate;

use App\Nova\Resource;

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

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $certificate = $this->ssl_certificate;

        return [
            Text::make('Website ID', 'id')->exceptOnForms(),

            Text::make('Dealer ID', 'dealer_id')->sortable(),

            Text::make('App ID', 'identifier')->exceptOnForms(),

            new Panel('Domain', [
                Boolean::make('Certified', function () use ($certificate) {
                    return $certificate ? $certificate->isValid() : null;
                }),

                Text::make('Domain')
                    ->sortable(),

                Text::make('Issuer', function () use ($certificate) {
                    return $certificate ? $certificate->getIssuer() : null;
                })
                    ->hideFromIndex()
                    ->exceptOnForms(),

                DateTime::make('Valid From', function () use ($certificate) {
                    return $certificate ? $certificate->validFromDate() : null;
                })
                    ->hideFromIndex()
                    ->exceptOnForms()
                    ->format('DD MMM, YYYY - LT'),

                DateTime::make('Expiration Date', function () use ($certificate) {
                    return $certificate ? $certificate->expirationDate() : null;
                })
                    ->hideFromIndex()
                    ->exceptOnForms()
                    ->format('DD MMM, YYYY - LT')
            ]),

            Select::make('Type', 'type')
                ->options($this->websiteTypes())
                ->displayUsingLabels(),

            Text::make('Template')->help("This will apply as the CertificateName on SSL certificates")->hideFromIndex(),

            Text::make('Template', 'template')->hideFromIndex()->sortable(),

            Boolean::make('Active', 'is_active')->sortable(),

            Boolean::make('Responsive', 'responsive')->sortable(),

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
            app()->make(IssueCertificateSsl::class)
                ->canSee(function ($request) {
                    return !$this->ssl_certificate;
                }),
            app()->make(EnableProxiedDomainsSsl::class)
        ];
    }
}
