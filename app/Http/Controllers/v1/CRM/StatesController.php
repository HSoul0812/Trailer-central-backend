<?php

namespace App\Http\Controllers\v1\CRM;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\GetStatesRequest;

class StatesController extends RestfulController
{
    /**
     * @const array
     */
    const STATES_LIST = [
        'AL' => 'ALABAMA',
        'AK' => 'ALASKA',
        'AS' => 'AMERICAN SAMOA',
        'AZ' => 'ARIZONA',
        'AR' => 'ARKANSAS',
        'CA' => 'CALIFORNIA',
        'CO' => 'COLORADO',
        'CT' => 'CONNECTICUT',
        'DE' => 'DELAWARE',
        'DC' => 'DISTRICT OF COLUMBIA',
        'FM' => 'FEDERATED STATES OF MICRONESIA',
        'FL' => 'FLORIDA',
        'GA' => 'GEORGIA',
        'GU' => 'GUAM GU',
        'HI' => 'HAWAII',
        'ID' => 'IDAHO',
        'IL' => 'ILLINOIS',
        'IN' => 'INDIANA',
        'IA' => 'IOWA',
        'KS' => 'KANSAS',
        'KY' => 'KENTUCKY',
        'LA' => 'LOUISIANA',
        'ME' => 'MAINE',
        'MH' => 'MARSHALL ISLANDS',
        'MD' => 'MARYLAND',
        'MA' => 'MASSACHUSETTS',
        'MI' => 'MICHIGAN',
        'MN' => 'MINNESOTA',
        'MS' => 'MISSISSIPPI',
        'MO' => 'MISSOURI',
        'MT' => 'MONTANA',
        'NE' => 'NEBRASKA',
        'NV' => 'NEVADA',
        'NH' => 'NEW HAMPSHIRE',
        'NJ' => 'NEW JERSEY',
        'NM' => 'NEW MEXICO',
        'NY' => 'NEW YORK',
        'NC' => 'NORTH CAROLINA',
        'ND' => 'NORTH DAKOTA',
        'MP' => 'NORTHERN MARIANA ISLANDS',
        'OH' => 'OHIO',
        'OK' => 'OKLAHOMA',
        'OR' => 'OREGON',
        'PW' => 'PALAU',
        'PA' => 'PENNSYLVANIA',
        'PR' => 'PUERTO RICO',
        'RI' => 'RHODE ISLAND',
        'SC' => 'SOUTH CAROLINA',
        'SD' => 'SOUTH DAKOTA',
        'TN' => 'TENNESSEE',
        'TX' => 'TEXAS',
        'UT' => 'UTAH',
        'VT' => 'VERMONT',
        'VI' => 'VIRGIN ISLANDS',
        'VA' => 'VIRGINIA',
        'WA' => 'WASHINGTON',
        'WV' => 'WEST VIRGINIA',
        'WI' => 'WISCONSIN',
        'WY' => 'WYOMING',
        'AE' => 'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST',
        'AA' => 'ARMED FORCES AMERICA (EXCEPT CANADA)',
        'AP' => 'ARMED FORCES PACIFIC'
    ];

    /**
     * Return States
     * 
     * @param Request $request
     * @return type
     */
    public function index(Request $request) {
        $request = new GetStatesRequest($request->all());

        if ($request->validate()) {             
            return $this->response->array([
                'data' => $this->getStates()
            ]);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Get States Array
     * 
     * @return array of states
     */
    private function getStates() {
        // Get States
        $statesList = self::STATES_LIST;

        // Loop States
        $states = array();
        foreach($statesList as $abbr => $state) {
            $states[] = ['id' => $abbr, 'name' => $state];
        }

        // Return States Array
        return $states;
    }
}
