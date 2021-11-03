<?php
namespace App\Services\Website;


interface WebsiteConfigServiceInterface
{
    public function getShowroomConfig(array $params);

    public function createShowroomConfig(array $requestData);

    public function updateShowroomConfig(array $requestData);
}