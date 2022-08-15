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

    public function getTextrailPlaceholderImage(): ?array
    {
      return $this->provider->getTextrailPlaceholderImage();
    }

    public function getTextrailTotalPartsCount(int $pageSize = 1, int $currentPage = 1): int
    {
        return $this->provider->getTextrailTotalPartsCount($pageSize, $currentPage);
    }

    public function getTextrailDumpStock(): array
    {
        return $this->provider->getTextrailDumpStock();
    }

    public function getAllCategories(): array
    {
        return $this->provider->getTextrailCategories();
    }

    public function getParentAndCategory(int $category_id): array
    {
        return $this->provider->getTextrailParentCategory($category_id);
    }

    public function getAttributes(): array
    {
        return  $this->provider->getAttributes();
    }

    public function getAttribute(string $code): array
    {
        return $this->provider->getAttribute($code);
    }
}
