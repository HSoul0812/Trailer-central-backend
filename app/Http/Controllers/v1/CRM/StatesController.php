<?php

namespace App\Http\Controllers\v1\CRM;

use App\Helpers\GeographyHelper;
use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\GetStatesRequest;

class StatesController extends RestfulController
{
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
        $statesList = GeographyHelper::STATES_LIST;

        // Loop States
        $states = array();
        foreach($statesList as $abbr => $state) {
            $states[] = ['id' => $abbr, 'name' => $state];
        }

        // Return States Array
        return $states;
    }
}
