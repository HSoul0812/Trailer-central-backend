<?php

namespace App\Repositories\Feed;

use App\Repositories\Repository;

interface TransactionExecuteQueueRepositoryInterface extends Repository
{
    /**
     * Stores the bulk data in the DB and returns an array of VINs
     * successfully stored
     * 
     * Meant to be used for operation types of type insert
     * 
     * Example array
       [
            [
              "stock": "yes",
              "vin": "4P5GF242521046429",
              "model": "LD",
              "year": "",
              "manufacture": "PJ Trailers",
              "brand": "PJ Trailers",
              "condition": "New",
              "msrp": 10987.5,
              "price": 9876.5,
              "category": "Flatdecks",
              "dealer": [
                "id": "pjdeal",
                "name": "PJ Dealer",
                "email": "hello@pjdealer.com",
                "location": [
                  [
                    "street": "123 Trailer Road",
                    "city": "Trailer Town",
                    "state": "TX",
                    "zip": "75486-1234",
                    "phone": "+1-555-666-7777"
                  ]
                ]
              ],
              "color": [
                "interior": "Black",
                "exterior": "Black"
              ],
              "attributes": [
                "length": 15.5,
                "width": 15.5,
                "gvwr": 1000,
                "axle_capacity": 5,
                "axle_count": 2,
                "hitch_type": "Gooseneck"
              ],
              "ship_date": [],
              "description": "Low-Pro Flatdeck With Duals",
              "comments": "Additional comments may be placed here",
              "photos": "https://www.google.com/1.jpg,https://www.google.com/2.jpg,https://www.google.com/3.jpg",
              "source": "ATW"
            ]
          ]
     */
    public function createBulk(array $atwInventoryData): array;
    
    /**
     * Stores the bulk data in the DB and returns an array of VINs
     * successfully stored
     * 
     * Meant to be used for operation types of type update
     * 
     * Example array
     * 
     * [
            [
              "vin": "4P5GF242521046429",
              "msrp": 10987.5,
              "price": 9876.5,
              "category": "Flatdecks",
              "dealer": [
                "id": "pjdeal",
                "name": "PJ Dealer",
                "email": "hello@pjdealer.com",
                "location": [
                  [
                    "street": "123 Trailer Road",
                    "city": "Trailer Town",
                    "state": "TX",
                    "zip": "75486-1234",
                    "phone": "+1-555-666-7777"
                  ]
                ]
              ],
              "description": "Low-Pro Flatdeck With Duals",
              "photos": "https://www.google.com/1.jpg,https://www.google.com/2.jpg,https://www.google.com/3.jpg",
              "source": "ATW"
            ]
          ]
     */
    public function updateBulk(array $atwInventoryData): array;
}
