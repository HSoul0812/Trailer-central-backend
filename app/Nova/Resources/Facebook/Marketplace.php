<?php

namespace App\Nova\Resources\Facebook;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class Marketplace extends Resource
{
    public static $group = 'Marketplaces';
    public static $orderBy = ['id' => 'asc'];
    public static $search = [
        'id', 'dealer_id', 'dealer_location_id', 'fb_username'
    ];

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Marketing\Facebook\Marketplace';

    public static function label(): string
    {
        return 'Facebook Integrations';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Number::make('Integration ID', 'id')
                ->sortable()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]]),
            Number::make('Dealer ID', 'dealer_id')
                ->sortable()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]]),
            BelongsTo::make('Dealer', 'user', 'App\Nova\Resources\Dealer\Dealer')
                ->sortable()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]]),
            Number::make('Dealer Location', 'dealer_location_id')
                ->sortable()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]]),
            Number::make('Custom Posts per Day', 'posts_per_day')
                ->max(15)
                ->sortable(),
            Text::make('Facebook Username', 'fb_username')
                ->sortable()
                ->withMeta(['extraAttributes' => [
                    'readonly' => true,
                    'disabled' => true,
                ]]),
        ];
    }
}
