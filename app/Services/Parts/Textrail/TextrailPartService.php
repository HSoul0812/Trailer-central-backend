<?php

namespace App\Services\Parts\Textrail;

use App\Services\Ecommerce\DataProvider\Providers\TextrailPartsInterface;

class TextrailPartService implements TextrailPartServiceInterface
{
    /**
     * @var TextrailPartsInterface
     */
    private $provider;

    public function __construct(TextrailPartsInterface $provider)
    {
        $this->provider = $provider;
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

    public function getTextrailTotalPartsCount(int $pageSize = 1, int $currentPage = 1): int
    {
        return $this->provider->getTextrailTotalPartsCount($pageSize, $currentPage);
    }
}
