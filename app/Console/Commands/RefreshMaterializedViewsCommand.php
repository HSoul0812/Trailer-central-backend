<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\LoggerServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;

class RefreshMaterializedViewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:refresh-views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh all materialized views';

    public function __construct(private ConnectionInterface $connection, private LoggerServiceInterface $logger)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->logger->info('[RefreshMaterializedViewsCommand] db:refresh-views');

        $viewsToMaterialize = [
            'inventory_price_average_per_day',
            'inventory_price_average_per_month',
            'inventory_price_average_per_quarter',
            'inventory_price_average_per_week',
            'inventory_price_average_per_year',
            'inventory_stock_average_per_day',
            'inventory_stock_average_per_month',
            'inventory_stock_average_per_quarter',
            'inventory_stock_average_per_week',
            'inventory_stock_average_per_year',
            'leads_average_per_day',
            'leads_average_per_month',
            'leads_average_per_quarter',
            'leads_average_per_week',
            'leads_average_per_year',
        ];

        foreach ($viewsToMaterialize as $viewName) {
            $this->logger->info("[RefreshMaterializedViewsCommand] materializing `$viewName`");
            $this->info("[RefreshMaterializedViewsCommand] materializing `$viewName`");

            $this->connection->statement("REFRESH MATERIALIZED VIEW $viewName");
        }
    }
}
