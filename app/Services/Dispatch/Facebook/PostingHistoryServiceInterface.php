<?php

namespace App\Services\Dispatch\Facebook;

interface PostingHistoryServiceInterface
{
    /**
     * Export and get the URL of the file for a given Integration
     * @param $id int
     * @param $fileName string
     * @return string
     */
    public function export(int $id, string $fileName): string;
}
