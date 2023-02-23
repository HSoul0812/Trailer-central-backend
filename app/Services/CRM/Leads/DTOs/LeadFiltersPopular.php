<?php

namespace App\Services\CRM\Leads\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Carbon\Carbon;

/**
 * Class LeadFiltersPopular
 *
 * @package App\Services\CRM\Leads\DTOs
 */
class LeadFiltersPopular
{
    use WithConstructor, WithGetter;


    /**
     * @const string
     */
    const FILTER_INTERACTION = 'interaction';

    /**
     * @const string
     */
    const FILTER_INTERACTED = 'interacted';

    /**
     * @const string
     */
    const FILTER_STATUS = 'lead_status';

    /**
     * @const array{type: string}
     */
    const FILTER_PREFIXES = [
        self::FILTER_INTERACTION => 'next_contact',
        self::FILTER_INTERACTED  => 'interacted'
    ];


    /**
     * @const string
     */
    const FILTER_TIME_TODAY = 'today';

    /**
     * @const string
     */
    const FILTER_TIME_YESTERDAY = 'yesterday';

    /**
     * @const string
     */
    const FILTER_TIME_WEEK = 'week';

    /**
     * @const string
     */
    const FILTER_TIME_NO = 'no';

    /**
     * @const array<string>
     */
    const FILTER_TIMES = [
        self::FILTER_TIME_TODAY,
        self::FILTER_TIME_YESTERDAY,
        self::FILTER_TIME_WEEK,
        self::FILTER_TIME_NO
    ];


    /**
     * @var string
     */
    private $label;

    /**
     * @var string (interaction | interacted)
     */
    private $type;

    /**
     * @var string (today | yesterday | week | no)
     */
    private $time;

    /**
     * @var string (next_contact | interacted)
     */
    private $prefix;


    /**
     * Fill Popular Lead Filter
     * 
     * @param array{label: string,
     *              type: string,
     *              time: string}
     * @return LeadFiltersPopular
     */
    public static function fill(array $params): LeadFiltersPopular {
        // Add Prefix
        if(isset($params['type'])) {
            $params['prefix'] = self::FILTER_PREFIXES[$params['type']];
        } else {
            $params['type'] = null;
            $params['prefix'] = null;
        }

        // No Time Frame?
        if(!isset($params['time'])) {
            $params['time'] = null;
        }

        // Return LeadFiltersPopular
        return new self($params);
    }


    /**
     * Calculate Filters From the Provided Options
     * 
     * @return array{filter: string}
     */
    public function calculateFilters(): array {
        // Initialize Filters
        $filters = [];

        // Determine Filters By Time
        switch($this->time) {
            case self::FILTER_TIME_NO:
                $filters[self::FILTER_STATUS] = 'Uncontacted';
            break;
            case self::FILTER_TIME_TODAY:
                $filters[$this->prefix . '_from'] = Carbon::now()->startOfDay()->toDateTimeString();
                $filters[$this->prefix . '_to']   = Carbon::now()->endOfDay()->toDateTimeString();
            break;
            case self::FILTER_TIME_PAST:
                $filters[$this->prefix . '_from'] = Carbon::now()->subDay()->startOfDay()->toDateTimeString();
                $filters[$this->prefix . '_to']   = Carbon::now()->subDay()->endOfDay()->toDateTimeString();
            break;
            case self::FILTER_TIME_WEEK:
                $filters[$this->prefix . '_from'] = Carbon::now()->startOfWeek()->toDateTimeString();
                $filters[$this->prefix . '_to']   = Carbon::now()->endOfWeek()->toDateTimeString();
            break;
        }

        // Return Filters Array
        return $filters;
    }
}