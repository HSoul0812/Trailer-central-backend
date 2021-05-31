<?php


namespace App\Console\Commands\CRM\Dms;

use App\Models\User\User;
use App\Repositories\Dms\QuoteRepositoryInterface;
use Illuminate\Console\Command;

class GetCompletedSaleWithNoFullInvoice extends Command
{
    protected $signature = 'crm:dms:unit-sale:get-completed-sale-with-no-full-invoice {dealerId?}';

    protected $description = 'Retrieves all completed deals with no full invoice';

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(QuoteRepositoryInterface $quoteRepository)
    {
        parent::__construct();
        $this->quoteRepository = $quoteRepository;
    }

    public function handle()
    { 
        $dealers = User::where('is_dms_active', 1)->where('dealer_id', '!=', 1001)->get();
        foreach($dealers as $dealer) {
            $deals = $this->quoteRepository->getCompletedDeals($dealer->dealer_id);     
            foreach($deals as $deal) {
                $invoices = $deal->invoice()->where('doc_num', 'LIKE', '%DP-%')->get();
                if ( $invoices->count() === $deal->invoice->count() ) {
                    $this->info('"'.$deal->title.'",'.$dealer->dealer_id.','.'"'.$deal->created_at.'"');
                }            
            }
        }        
    }
}
