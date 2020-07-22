<?php

namespace App\Repositories\User;

use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\User\DealerLocation;

class DealerLocationRepository implements DealerLocationRepositoryInterface {
    
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        $query = DealerLocation::select('*');
        
        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        return $query->paginate($params['per_page'])->appends($params);        
    }

    public function update($params) {
        throw new NotImplementedException;
    }


    /**
     * Get All Dealer SMS Numbers
     * 
     * @param int $dealerId
     * @return type
     */
    public function findAllDealerSmsNumbers($dealerId)
    {
        // Get All Dealer Location SMS Numbers
        return DealerLocation::get(['sms_phone', 'dealer_location_id'])
            ->where('dealer_id', $dealerId)
            ->whereNotNull('sms_phone')
            ->all();
    }

    /**
     * Get Dealer Number for Location or Default
     * 
     * @param int $dealerId
     * @param int $locationId
     * @return type
     */
    public function findDealerNumber($dealerId, $locationId) {
        // Get Dealer Location
        $location = DealerLocation::find($locationId);
        if(!empty($location->sms_phone)) {
            return $location->sms_phone;
        }

        // Get Numbers By Dealer ID
        if(!empty($location->dealer_id)) {
            $numbers = $this->findAllDealerSmsNumbers($location->dealer_id);
        } else {
            $numbers = $this->findAllDealerSmsNumbers($dealerId);
        }

        // Loop Numbers
        $phoneNumber = '';
        if(!empty($numbers)) {
            // Get First Valid Number!
            foreach($numbers as $number) {
                if(!empty($number->sms_phone)) {
                    $phoneNumber = $number->sms_phone;
                    break;
                }
            }
        }

        // Return Phone Number
        return $phoneNumber;
    }

}
