<?php

namespace App\Services\Parts\Textrail;



interface TextrailPartServiceInterface {

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
}