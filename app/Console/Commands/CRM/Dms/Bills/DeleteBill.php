<?php

namespace App\Console\Commands\CRM\Dms\Bills;

use App\Repositories\Dms\Quickbooks\BillRepositoryInterface;
use Illuminate\Console\Command;
use Throwable;

class DeleteBill extends Command
{
    protected $signature = 'crm:dms:bills:delete {billId}';

    protected $description = 'Delete the bill and its related resources.';

    /** @var BillRepositoryInterface */
    private $billRepository;

    public function __construct(
        BillRepositoryInterface $billRepository
    )
    {
        parent::__construct();

        $this->billRepository = $billRepository;
    }

    public function handle(): int
    {
        $billId = $this->argument('billId');
        
        if (!$this->confirm("Are you sure you want to delete bill ID $billId?")) {
            $this->info("Well, thank you for visiting, come back when you're certain about the bill that you want to delete!");
            return 0;
        }
        
        try {
            $this->billRepository->delete([
                'id' => $billId,
            ]);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return 1;
        }
        
        $this->info("Bill ID $billId and its related resources are deleted!");

        return 0;
    }
}