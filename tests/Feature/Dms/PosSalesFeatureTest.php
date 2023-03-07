<?php

namespace Tests\Feature\Dms;

use Tests\TestCase;

/**
 * Class PosSalesFeatureTest
 * @package Tests\Feature\Dms
 * @todo add create, update, delete tests
 */
class PosSalesFeatureTest extends TestCase
{
    // use a known sample ID from the database with valid values
    protected $sampleId = 379;

    /**
     * @group DMS
     * @group DMS_POS
     *
     * @return void
     */
    public function testListHttpOk()
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/dms/pos/sales');

        $response->assertStatus(200);
    }

    /**
     * @group DMS
     * @group DMS_POS
     *
     * @return void
     */
    public function testListHasDataProperty()
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/dms/pos/sales');

        $json = json_decode($response->getContent(), true);
        $this->assertTrue(isset($json['data']));
    }

    /**
     * @group DMS
     * @group DMS_POS
     *
     * @return void
     */
    public function testShowHttpOk()
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/dms/pos/sales/' . $this->sampleId);

        $response->assertStatus(200);
    }

    /**
     * @group DMS
     * @group DMS_POS
     *
     * @return void
     */
    public function testShowCorrectObject()
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/dms/pos/sales/' . $this->sampleId);

        $json = json_decode($response->getContent(), true);

        // check each field for correct value from db
        $this->assertTrue(isset($json['data']));
        $this->assertTrue($json['data']['total'] === 4.2);
        $this->assertTrue($json['data']['amount_received'] === 4.2);
        $this->assertTrue($json['data']['subTotal'] === 3.75);
        $this->assertTrue($json['data']['discount'] === 0);
    }

    /**
     * @group DMS
     * @group DMS_POS
     *
     * @return void
     */
    public function testShowRelationRefunds()
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/dms/pos/sales/' . $this->sampleId . '?with=refunds');

        $json = json_decode($response->getContent(), true);

        // check if relation exists
        $this->assertTrue(isset($json['data']['refunds']));
    }

    /**
     * @group DMS
     * @group DMS_POS
     *
     * @return void
     */
    public function testShowRelationProducts()
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/dms/pos/sales/' . $this->sampleId . '?with=products');

        $json = json_decode($response->getContent(), true);

        // check if relation exists
        $this->assertTrue(isset($json['data']['products']));

        // sale should have products
        $this->assertTrue(count($json['data']['products']) > 0);
    }

}
