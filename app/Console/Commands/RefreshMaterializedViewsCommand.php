<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\LoggerServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;

class RefreshMaterializedViewsCommand extends Command
{
    public const RECURRENCE_DAILY = 'daily';
    public const RECURRENCE_WEEKLY = 'weekly';
    public const RECURRENCE_DEFAULT = self::RECURRENCE_DAILY;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:refresh-views {recurrence=daily}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Refresh the materialized views defined on 'config/materializedviews.php' with a desired recurrence";

    public function __construct(private ConnectionInterface $connection, private LoggerServiceInterface $logger)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $recurrence = $this->getRecurrence();

        $this->logger->info("[RefreshMaterializedViewsCommand] db:refresh-views {{$recurrence}}");

        foreach (config("materializedviews.$recurrence") as $viewName) {
            $this->logger->info("[RefreshMaterializedViewsCommand] materializing `$viewName`");

            $this->connection->statement("REFRESH MATERIALIZED VIEW $viewName");
        }
    }

    private function getRecurrence(): string
    {
        $recurrence = $this->argument('recurrence');

        return in_array($recurrence, [self::RECURRENCE_DAILY, self::RECURRENCE_WEEKLY], true) ?
            $recurrence :
            self::RECURRENCE_DEFAULT;
    }
}
