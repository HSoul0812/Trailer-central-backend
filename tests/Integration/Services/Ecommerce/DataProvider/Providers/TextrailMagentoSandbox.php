<?php

namespace Tests\Integration\Services\Ecommerce\DataProvider\Providers;

use App\Services\Ecommerce\DataProvider\Providers\TextrailPartsInterface;
use App\Services\Parts\Textrail\DTO\TextrailPartDTO;

class TextrailMagentoSandbox implements TextrailPartsInterface
{
  public function getAllParts(int $currentPage = 1, int $pageSize = 1000): array
  {
    $Allparts = [];
    $images = [['position' => '1', 'file' => '/test']];

    $dtoTextrail = TextrailPartDTO::from([
      'id' => 1,
      'sku' => 7950481,
      'title' => 'item 1',
      'price' => 2,
      'weight' => 10,
      'description' => 'description 1',
      'category_id' => 1,
      'manufacturer_id' =>  1,
      'brand_id' => 1,
      'images' => $images
    ]);

    $dtoTextrail2 = TextrailPartDTO::from([
      'id' => 2,
      'sku' => 7950481,
      'title' => 'item 2',
      'price' => 2,
      'weight' => 20,
      'description' => 'description 2',
      'category_id' => 1,
      'manufacturer_id' =>  1,
      'brand_id' => 1,
      'images' => $images
    ]);

    array_push($Allparts, $dtoTextrail);
    array_push($Allparts, $dtoTextrail2);

    return $Allparts;
  }

  public function getTextrailCategory(int $categoryId): object
  {

    if ($categoryId == 1) {
      $categoryJson = '{"name": "test category", "parent_id": 2}';
    } elseif ($categoryId == 2) {
      $categoryJson = '{"name": "test category for type"}';
    } else {
      $categoryJson = '{}';
    }

    return json_decode($categoryJson);
  }

  public function getTextrailManufacturers(): array
  {
    $manufacturesJson = '[{"label":"test manufacturer","value":1}]';
    return json_decode($manufacturesJson);
  }

  public function getTextrailBrands(): array
  {
    $brandsJson = '[{"label":"test brand","value":1}]';
    return json_decode($brandsJson);
  }

  public function getTextrailImage(array $img): ?array
  {
    $img_url = $this->getTextrailImagesBaseUrl() . $img['file'];
    $checkFile = get_headers($img_url);

    if ($checkFile[0] == "HTTP/1.1 200 OK") {
      $imageData = file_get_contents($img_url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
      $explodedImage = explode('/', $img['file']);
      $fileName = $explodedImage[count($explodedImage) - 1];

      return ['imageData' => $imageData, 'fileName' => $fileName];
    } else {
      return null;
    }
  }

  /**
   * @return null|array{imageData: array, fileName: string}
   */
  public function getTextrailPlaceholderImage(): ?array
  {
    $img_url = $this->getTextrailImagesBaseUrl() . self::TEXTRAIL_ATTRIBUTES_PLACEHOLDER_URL;

    $checkFile = get_headers($img_url);

    if ($checkFile[0] == "HTTP/1.1 200 OK") {
      $imageData = file_get_contents($img_url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
      $explodedImage = explode('/', self::TEXTRAIL_ATTRIBUTES_PLACEHOLDER_URL);
      $fileName = $explodedImage[count($explodedImage) - 1];

      return ['imageData' => $imageData, 'fileName' => $fileName];
    }

    return null;
  }

  protected function getTextrailImagesBaseUrl(): string{
    return 'https://upload.wikimedia.org';
  }

  public function getTextrailTotalPartsCount(int $pageSize = 1, int $currentPage = 1): int
  {
   return 0;
  }

    public function getTextrailDumpStock(): array
    {
        $stocks['7950084'] = [
            "qty" => 10,
            "is_salable" => true
        ];

        $stocks['7950481'] = [
            "qty" => 10,
            "is_salable" => true
        ];

        return $stocks;
    }

    public function getTextrailCategories(): array
    {
       return [];
    }

    public function getTextrailParentCategory(int $category_id): array
    {
        $categoryJson = '{"name": "test category", "parent_id": 2}';
        $parentJson =  '{"name": "parent category", "parent_id": 1}';

        return [['name' => 'parent category'], ['name' => 'category']];
    }
}
