<?php
declare(strict_types=1);

namespace App\Http\Controllers\v1\Geocode;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;

class GeocodeController extends AbstractRestfulController {
    public function autocomplete() {
        return $this->response->array([
            ['label' => '777 Brockton Avenue, Abington MA 2351'],
            ['label' => '30 Memorial Drive, Avon MA 2322'],
            ['label' => '250 Hartford Avenue, Bellingham MA 2019'],
            ['label' => '700 Oak Street, Brockton MA 2301'],
            ['label' => '66-4 Parkhurst Rd, Chelmsford MA 1824'],
        ]);
    }

    public function geocode() {
        return $this->response->array([
            'label' => 'William S Canning Blvd, Fall River, MA 02721, United States',
            'position' => [
                'lat' => 41.67052,
                'lng' => -71.16053
            ]
        ]);
    }

    public function reverse() {
        return $this->response->array([
            'label' => 'William S Canning Blvd, Fall River, MA 02721, United States',
            'address' => [
                'countryCode'=> 'USA',
                'countryName'=> 'United States',
                'stateCode'=> 'MA',
                'state'=>  'Massachusetts',
                'county'=> 'Bristol',
                'city'=> 'Fall River',
                'district'=> 'Maplewood',
                'street'=> 'William S Canning Blvd',
                'postalCode'=> '02721'
            ],
            'position' => [
                'lat' => 41.67052,
                'lng' => -71.16053
            ]
        ]);
    }

    public function index(IndexRequestInterface $request)
    {
        return new NotImplementedException();
    }

    public function create(CreateRequestInterface $request)
    {
        return new NotImplementedException();
    }

    public function show(int $id)
    {
        return new NotImplementedException();
    }

    public function update(int $id, UpdateRequestInterface $request)
    {
        return new NotImplementedException();
    }

    public function destroy(int $id)
    {
        return new NotImplementedException();
    }

    protected function constructRequestBindings(): void
    {
    }
}
