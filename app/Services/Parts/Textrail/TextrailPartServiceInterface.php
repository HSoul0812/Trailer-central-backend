<?php

namespace App\Services\Parts\Textrail;



interface TextrailPartServiceInterface {

  public function getAllParts();
  
  public function getTextrailCategory($categoryId);
  
  public function getTextrailManufacturers();
  
  public function getTextrailBrands();
  
  public function getTextrailImage($img);
}