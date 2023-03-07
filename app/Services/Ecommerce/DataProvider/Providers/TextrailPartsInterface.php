<?php

namespace App\Services\Ecommerce\DataProvider\Providers;

use App\Services\Parts\Textrail\DTO\TextrailPartDTO;

interface TextrailPartsInterface
{
    /**
     * @param int $currentPage
     * @param int $pageSize
     * @return array<TextrailPartDTO>
     */
    public function getAllParts(int $currentPage = 1, int $pageSize = 1000): array;

    public function getTextrailCategory(int $categoryId): object;

    public function getTextrailManufacturers(): array;

    public function getTextrailBrands(): array;

    public function getTextrailImage(array $img): ?array;

    public function getTextrailPlaceholderImage(): ?array;

    public function getTextrailTotalPartsCount(int $pageSize = 1, int $currentPage = 1): int;

    public function getTextrailDumpStock(): array;

    public function getAttributes(): array;

    public function getAttribute(string $code): array;

    public function getTextrailCategories(): array;

    public function getTextrailParentCategory(int $category_id): array;
}
