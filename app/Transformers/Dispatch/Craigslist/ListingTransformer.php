<?php

namespace App\Transformers\Dispatch\Craigslist;

use App\Services\Dispatch\Craigslist\DTOs\ClappListing;
use App\Transformers\Marketing\Craigslist\DraftTransformer;
use App\Transformers\Marketing\Craigslist\PostTransformer;
use App\Transformers\Marketing\Craigslist\ActivePostTransformer;
use App\Transformers\Marketing\Craigslist\TransactionTransformer;
use App\Transformers\Marketing\Craigslist\SessionTransformer;
use App\Transformers\Marketing\Craigslist\QueueTransformer;
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
        'session',
        'queue'
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
     * @param DraftTransformer $draftTransformer
     * @param PostTransformer $postTransformer
     * @param ActivePostTransformer $activePostTransformer
     * @param TransactionTransformer $transactionTransformer
     * @param SessionTransformer $sessionTransformer
     * @param QueueTransformer $queueTransformer
     */
    public function __construct(
        DraftTransformer $draftTransformer,
        PostTransformer $postTransformer,
        ActivePostTransformer $activePostTransformer,
        TransactionTransformer $transactionTransformer,
        SessionTransformer $sessionTransformer,
        QueueTransformer $queueTransformer
    ) {
        $this->draftTransformer = $draftTransformer;
        $this->postTransformer = $postTransformer;
        $this->activePostTransformer = $activePostTransformer;
        $this->transactionTransformer = $transactionTransformer;
        $this->sessionTransformer = $sessionTransformer;
        $this->queueTransformer = $queueTransformer;
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
        return $this->item($listing->draft, $this->draftTransformer);
    }

    public function includePost(ClappListing $listing)
    {
        return $this->item($listing->post, $this->postTransformer);
    }

    public function includeActivePost(ClappListing $listing)
    {
        return $this->item($listing->activePost, $this->activePostTransformer);
    }

    public function includeTransaction(ClappListing $listing)
    {
        return $this->item($listing->transaction, $this->transactionTransformer);
    }

    public function includeSession(ClappListing $listing)
    {
        return $this->item($listing->session, $this->sessionTransformer);
    }

    public function includeQueue(ClappListing $listing)
    {
        return $this->item($listing->queue, $this->queueTransformer);
    }
}