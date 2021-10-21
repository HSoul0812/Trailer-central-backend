<?php
namespace Tests\Integration\Services\Ecommerce\DataProvider\Providers;

use App\Services\Ecommerce\DataProvider\DataProviderInterface;
use Dingo\Api\Http\Response;
use GuzzleHttp\Client as GuzzleHttpClient;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use App\Services\Parts\Textrail\DTO\TextrailPartDTO;
use App\Services\Ecommerce\DataProvider\Providers\TextrailMagentoInterface;

class TextrailMagentoSandbox implements DataProviderInterface, TextrailMagentoInterface
{

  public function createCustomer(array $params): array
  {
      return null;
  }

  public function generateAccessToken(array $credentials)
  {
      return null;
  }

  public function addItemToCart(array $params, int $quoteId)
  {
      return null;
  }

  public function estimateShippingCost(array $params)
  {
      return null;
  }

  public function createQuote(): int
  {
      return null;
  }

  public function getAllParts(): array
  {
    $Allparts = [];
    $images = [['position' => '1', 'file' => '/test']];
    
    $dtoTextrail = TextrailPartDTO::from([
      'id' => 1,
      'sku' => 1111,
      'title' => 'item 1',
      'price' => 1,
      'weight' => 10,
      'description' => 'description 1',
      'category_id' => 1,
      'manufacturer_id' =>  1,
      'brand_id' => 1,
      'images' => $images
    ]);
    
    $dtoTextrail2 = TextrailPartDTO::from([
      'id' => 2,
      'sku' => 2222,
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

  public function getTextrailImagesBaseUrl(): string{
    return 'https://upload.wikimedia.org';
  }

} 