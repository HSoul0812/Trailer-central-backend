<?php

declare(strict_types=1);

return [
    /*
     |--------------------------------------------------------------------------
     | Materialized views
     |--------------------------------------------------------------------------
     |
     | This option controls the desired materialized views recurrence refreshing
     |
     */

    'daily' => explode(',', env('MATERIALIZED_VIEWS_DAILY_REFRESH')),

    'weekly' => explode(',', env('MATERIALIZED_VIEWS_WEEKLY_REFRESH')),
];
