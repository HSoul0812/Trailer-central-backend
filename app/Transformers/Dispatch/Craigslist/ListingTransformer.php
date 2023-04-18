<?php

namespace App\Transformers\Dispatch\Craigslist;

use App\Services\Dispatch\Craigslist\DTOs\ClappListing;
use App\Transformers\Marketing\Craigslist\DraftTransformer;
use App\Transformers\Marketing\Craigslist\PostTransformer;
use App\Transformers\Marketing\Craigslist\ActivePostTransformer;
use App\Transformers\Marketing\Craigslist\TransactionTransformer;
use App\Transformers\Marketing\Craigslist\SessionTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class ListingTransformer
 * 
 * @package App\Transformers\Dispatch\Craigslist
 */
class ListingTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'draft',
        'post',
        'activePost',
        'transaction',
        'session'
    ];


    /**
     * @var DraftTransformer
     */
    protected $draftTransformer;

    /**
     * @var PostTransformer
     */
    protected $postTransformer;

    /**
     * @var ActivePostTransformer
     */
    protected $activePostTransformer;

    /**
     * @var TransactionTransformer
     */
    protected $transactionTransformer;

    /**
     * @var SessionTransformer
     */
    protected $sessionTransformer;

    /**
     * @param DraftTransformer $draftTransformer
     * @param PostTransformer $postTransformer
     * @param ActivePostTransformer $activePostTransformer
     * @param TransactionTransformer $transactionTransformer
     * @param SessionTransformer $sessionTransformer
     */
    public function __construct(
        DraftTransformer $draftTransformer,
        PostTransformer $postTransformer,
        ActivePostTransformer $activePostTransformer,
        TransactionTransformer $transactionTransformer,
        SessionTransformer $sessionTransformer
    ) {
        $this->draftTransformer = $draftTransformer;
        $this->postTransformer = $postTransformer;
        $this->activePostTransformer = $activePostTransformer;
        $this->transactionTransformer = $transactionTransformer;
        $this->sessionTransformer = $sessionTransformer;
    }

    /**
     * @param ClappListing $listing
     * @return array
     */
    public function transform(ClappListing $listing): array
    {
        return [];
    }

    public function includeDraft(ClappListing $listing)
    {
        if(!empty($listing->draft)) {
            return $this->item($listing->draft, $this->draftTransformer);
        }
        return $this->null();
    }

    public function includePost(ClappListing $listing)
    {
        if(!empty($listing->post)) {
            return $this->item($listing->post, $this->postTransformer);
        }
        return $this->null();
    }

    public function includeActivePost(ClappListing $listing)
    {
        if(!empty($listing->activePost)) {
            return $this->item($listing->activePost, $this->activePostTransformer);
        }
        return $this->null();
    }

    public function includeTransaction(ClappListing $listing)
    {
        if(!empty($listing->transaction)) {
            return $this->item($listing->transaction, $this->transactionTransformer);
        }
        return $this->null();
    }

    public function includeSession(ClappListing $listing)
    {
        return $this->item($listing->session, $this->sessionTransformer);
    }
}