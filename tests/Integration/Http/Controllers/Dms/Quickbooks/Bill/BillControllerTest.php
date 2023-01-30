<?php

namespace Tests\Integration\Http\Controllers\Dms\Quickbooks\Bill;

use App\Exceptions\Tests\MissingTestDealerIdException;
use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\Parts\Vendor;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class BillControllerTest extends TestCase
{
    private $dealerId;

    private $dealerLocationId;

    private $accessToken;

    /**
     * @throws MissingTestDealerIdException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dealerId = $this->getTestDealerId();
        $this->dealerLocationId = $this->getTestDealerLocationId();
        $this->accessToken = $this->accessToken();
    }

    /**
     * Ensure the creates bill route is working with the DocNum check
     *
     * @group DMS
     * @group DMS_BILLS
     *
     * @return void
     * @throws MissingTestDealerIdException
     */
    public function testCreateBillDocNumCheckWorks()
    {
        $dealerId = $this->getTestDealerId();
        $docNum = Str::random();

        /** @var Vendor $vendor */
        $vendor = factory(Vendor::class)->create([
            'dealer_id' => $dealerId,
        ]);

        /** @var Vendor $vendor */
        $vendorWithoutBill = factory(Vendor::class)->create([
            'dealer_id' => $dealerId,
        ]);

        // Given that we have a bill with this vendor already
        factory(Bill::class)->create([
            'dealer_id' => $dealerId,
            'doc_num' => $docNum,
            'vendor_id' => $vendor->id,
        ]);

        // Then we try to create a new bill via HTTP using the same vendor and same doc_num
        // we need to assert that the creation has failed because of duplicate doc_num
        $this
            ->postJson(
                '/api/bills',
                $this->payload($docNum, $vendor->id),
                $this->headers()
            )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson($this->validationErrors());

        // Now we try again but with different vendor that has no bill before
        // we should be able to create this one successfully
        $this
            ->postJson(
                '/api/bills',
                $this->payload($docNum, $vendorWithoutBill->id),
                $this->headers()
            )
            ->assertStatus(Response::HTTP_OK);
    }

    /**
     * Ensure the updates bill route is working with the DocNum check
     *
     * @group DMS
     * @group DMS_BILLS
     *
     * @return void
     * @throws MissingTestDealerIdException
     */
    public function testUpdateBillDocNumCheckWorks()
    {
        $dealerId = $this->getTestDealerId();
        $dealerLocationId = $this->getTestDealerLocationId();
        $docNum = Str::random();
        $bill2DocNum = Str::random();

        /** @var Vendor $vendor */
        $vendor = factory(Vendor::class)->create([
            'dealer_id' => $dealerId,
        ]);

        // Given that we have a bill with this vendor already
        $bill = factory(Bill::class)->create([
            'dealer_id' => $dealerId,
            'doc_num' => $docNum,
            'vendor_id' => $vendor->id,
        ]);

        factory(Bill::class)->create([
            'dealer_id' => $dealerId,
            'doc_num' => $bill2DocNum,
            'vendor_id' => $vendor->id,
        ]);

        /** @var Vendor $vendor */
        $otherVendorWithSameDocNumBill = factory(Vendor::class)->create([
            'dealer_id' => $dealerId,
        ]);

        factory(Bill::class)->create([
            'dealer_id' => $dealerId,
            'doc_num' => $docNum,
            'vendor_id' => $otherVendorWithSameDocNumBill,
        ]);

        // We should be able to update the bill with its own information
        $this
            ->putJson(
                "/api/bills/$bill->id",
                $this->payload($docNum, $vendor->id),
                $this->headers()
            )
            ->assertStatus(Response::HTTP_OK);

        // Now we try again with the different doc_num, but in the same vendor
        // we shouldn't be able to update it
        $this
            ->putJson(
                "/api/bills/$bill->id",
                $this->payload($bill2DocNum, $vendor->id),
                $this->headers()
            )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson($this->validationErrors());

        // This time we change vendor, but use the same doc_num with bill that's created
        // under that vendor, we shouldn't be able to update
        $this
            ->putJson(
                "/api/bills/$bill->id",
                $this->payload($docNum, $otherVendorWithSameDocNumBill->id),
                $this->headers(),
            )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson($this->validationErrors());

        // This time to a different vendor as well but with a totally new doc_num
        // in this case we should be able to update
        $this
            ->putJson(
                "/api/bills/$bill->id",
                $this->payload(Str::random(), $otherVendorWithSameDocNumBill->id),
                $this->headers(),
            )
            ->assertStatus(Response::HTTP_OK);
    }

    /**
     * @return array
     */
    private function headers(): array
    {
        return [
            'access-token' => $this->accessToken,
        ];
    }

    /**
     * @param string $docNum
     * @param int $vendorId
     * @return array
     */
    public function payload(string $docNum, int $vendorId): array
    {
        return [
            'dealer_id' => $this->dealerId,
            'doc_num' => $docNum,
            'dealer_location_id' => $this->dealerLocationId,
            'vendor_id' => $vendorId,
            'total' => 10,
            'status' => 'due',
        ];
    }

    /**
     * @return array
     */
    private function validationErrors(): array
    {
        return [
            'message' => 'Validation Failed',
            'errors' => [
                'doc_num' => [
                    'The doc num has already been taken.',
                ],
            ],
        ];
    }
}
