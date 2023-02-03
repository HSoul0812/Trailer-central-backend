<?php

namespace App\Nova\Resources\Website;

use App\Nova\Metrics\DealerWebsitesUptime;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use App\Models\Website\Config\WebsiteConfig;

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
        return [
            Text::make('Website ID', 'id')->exceptOnForms(),

            Text::make('Dealer ID', 'dealer_id')->sortable(),

            Text::make('App ID', 'identifier')->exceptOnForms(),

            Text::make('Domain')
                ->sortable(),

            Text::make('Type')
                ->sortable(),

            Boolean::make('Active', 'is_active')->sortable(),

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

        ];
    }
}
