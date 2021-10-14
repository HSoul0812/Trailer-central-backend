<?php

namespace App\Services\Parts\Textrail;



interface TextrailPartServiceInterface {

  public function getAllParts();
  
  public function getTextrailCategory(int $categoryId);
  
  public function getTextrailManufacturers();
  
  public function getTextrailBrands();
  
  public function getTextrailImage(array $img);
}