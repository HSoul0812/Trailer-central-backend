<?php

namespace App\Services\Parts\Textrail;

use App\Services\Parts\Textrail\DTO\TextrailPartDTO;

interface TextrailPartServiceInterface
{

    /**
     * @return array<TextrailPartDTO>
     */
    public function getAllParts(): array;

    public function getTextrailCategory(int $categoryId): object;

    public function getTextrailManufacturers(): array;

    public function getTextrailBrands(): array;

    /**
     * @return null|array{imageData: array, fileName: string}
     */
    public function getTextrailImage(array $img): ?array;

    /**
     * @return null|array{imageData: array, fileName: string}
     */
    public function getTextrailPlaceholderImage(): ?array;

    public function getTextrailTotalPartsCount(int $pageSize = 1, int $currentPage = 1): int;

    public function getTextrailDumpStock(): array;

    public function getAttributes(): array;

    public function getAttribute(string $code): array;

    public function getAllCategories(): array;

    public function getParentAndCategory(int $category_id): array;
}
