<?php

namespace App\Services\Parts\Textrail;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Services\Parts\Textrail\DTO\TextrailPartDTO;
use App\Services\Ecommerce\DataProvider\Providers\TextrailMagento;


class TextrailPartService implements TextrailPartServiceInterface
{

    public function __construct()
    {
        $this->provider = new TextrailMagento();
    }

    public function getAllParts(int $currentPage = 1, int $pageSize = 1000): array
    {
      return $this->provider->getAllParts($currentPage , $pageSize);
  
    }

    public function getTextrailCategory(int $categoryId): object
    {
      return $this->provider->getTextrailCategory($categoryId);
    }

    public function getTextrailManufacturers(): array
    {
      return $this->provider->getTextrailManufacturers();
    }
      
    public function getTextrailBrands(): array
    {
      return $this->provider->getTextrailBrands();
    }

    public function getTextrailImage(array $img): ?array
    {
      return $this->provider->getTextrailImage($img);
    }
}