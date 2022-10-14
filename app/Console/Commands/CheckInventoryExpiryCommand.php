<?php

namespace App\Console\Commands;

use App\Services\LoggerServiceInterface;
use Illuminate\Console\Command;

class CheckInventoryExpiryCommand extends Command
{
    protected $signature = '';
    protected $description = '';

    public function __construct(private LoggerServiceInterface $logger)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->logger->info('[CheckInventoryExpiryCommand] ');
    }
}
