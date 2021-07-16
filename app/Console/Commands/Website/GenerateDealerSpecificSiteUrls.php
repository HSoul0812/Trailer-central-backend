<?php

namespace App\Console\Commands\Website;

use App\Services\Website\WebsiteDealerUrlServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class GenerateDealerSpecificSiteUrls
 * @package App\Console\Commands\Website
 */
class GenerateDealerSpecificSiteUrls extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "website:generate-dealer-specific-site-urls";

    /**
     * @var WebsiteDealerUrlServiceInterface
     */
    private $websiteDealerUrlsService;

    /**
     * GenerateDealerSpecificSiteUrls constructor.
     * @param WebsiteDealerUrlServiceInterface $websiteDealerUrlsService
     */
    public function __construct(WebsiteDealerUrlServiceInterface $websiteDealerUrlsService)
    {
        parent::__construct();

        $this->websiteDealerUrlsService = $websiteDealerUrlsService;
    }

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $result = $this->websiteDealerUrlsService->generate();

        foreach ($result as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $this->info(ucfirst(str_replace('_', ' ', Str::snake($key))) . ': ' . $value);
        }

        return true;
    }
}
