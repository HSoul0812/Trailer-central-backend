<?php


namespace App\Services\Import\Feed\Type;


interface FeedImporterInterface
{
    /**
     * return the feed code (short name)
     * @return string
     */
    public function feedCode();
}
