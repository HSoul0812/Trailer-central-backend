<?php

namespace App\Jobs\CRM\Email;

use App\Exceptions\CRM\Email\ScrapeRepliesJobFailedException;
use App\Jobs\Job;
use App\Models\User\NewDealerUser;
use App\Models\CRM\User\SalesPerson;
use App\Services\CRM\Email\ScrapeRepliesServiceInterface;
use Illuminate\Support\Facades\Cache;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ScrapeRepliesJob
 * 
 * @package App\Jobs\CRM\Email
 */
class ScrapeRepliesJob extends Job
{ 
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var NewDealerUser
     */
    private $dealer;

    /**
     * @var SalesPerson
     */
    private $salesperson;

    /**
     * ScrapeRepliesJob constructor.
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     */
    public function __construct(NewDealerUser $dealer, SalesPerson $salesperson)
    {
        $this->dealer = $dealer;
        $this->salesperson = $salesperson;
    }

    /**
     * @param ScrapeRepliesServiceInterface $service
     * @throws ScrapeRepliesJobFailedException
     * @return int
     */
    public function handle(ScrapeRepliesServiceInterface $service): int {
        // Initialize Logger
        $log = Log::channel('scrapereplies');

        // Try Catching Error for Sales Person
        try {
            // Import Emails
            $log->info('Dealer #' . $this->dealer->id . ', Sales Person #' .
                                $this->salesperson->id . ' - Starting Scrape Replies Job');
            $imports = $service->salesperson($this->dealer, $this->salesperson);

            // Adjust Total Import Counts
            $log->info('Dealer #' . $this->dealer->id . ', Sales Person #' .
                                $this->salesperson->id . ' - Finished Importing ' . $imports . ' Emails');

            // Delete the cache when the job is finished
            Cache::forget($this->cacheKey());

            return $imports;
        } catch (Exception $e) {
            $log->error('Dealer #' . $this->dealer->id . ' Sales Person #' .
                                $this->salesperson->id . ' - Exception returned: ' .
                                $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            throw new ScrapeRepliesJobFailedException;
        }
    }

    /**
     * Check if there is a pending job
     * @return bool
     * @throws Exception
     */
    public function hasPending(): bool
    {
        return Cache::get($this->cacheKey()) !== null;
    }

    /**
     * Check if there is no pending job
     * @return bool
     * @throws Exception
     */
    public function hasNoPending(): bool
    {
        return !$this->hasPending();
    }

    /**
     * Get cache key
     * @return string
     */
    public function cacheKey(): string
    {
        return "scrape-replies-{$this->dealer->id}-{$this->salesperson->id}";
    }
}
