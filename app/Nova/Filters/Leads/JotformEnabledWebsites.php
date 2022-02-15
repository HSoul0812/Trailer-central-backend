<?php

namespace App\Nova\Filters\Leads;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use App\Models\CRM\Leads\Jotform\WebsiteForms;
 
class JotformEnabledWebsites extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->where('website_id', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $websiteout = [];
        $websites = WebsiteForms::select('website_id')->groupBy('website_id')->get();
        foreach($websites as $website) {
            $websiteout[$website->website_id] = $website->website_id;
        }        
        
        return $websiteout;
    }
}
