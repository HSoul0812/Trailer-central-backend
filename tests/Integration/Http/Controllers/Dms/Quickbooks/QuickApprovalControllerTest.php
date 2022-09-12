<?php
namespace Tests\Integration\Http\Controllers\Dms\Quickbooks;

use App\Http\Controllers\v1\Dms\Quickbooks\QuickbookApprovalController;
use App\Http\Requests\Dms\Quickbooks\DeleteQuickbookApprovalRequest;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use Dingo\Api\Exception\ResourceException;
use Tests\database\seeds\Dms\Quickbook\QuickbookApprovalSeeder;
use Tests\database\seeds\Dms\Quickbook\QuickbookApprovalDeletedSeeder;
use Tests\TestCase;

class QuickApprovalControllerTest extends TestCase
{
    /** @var QuickbookApprovalSeeder  */
    private $qbaSeed;

    /** @var QuickbookApprovalDeletedSeeder  */
    private $qbaDelSeed;

    /**
     * @covers ::index
     *
     * @group quickbook
     * @group DMS
     * @group DMS_QUICKBOOK
     */
    public function testIndexWithToSendStatus() {
        $response = $this->json(
            'GET',
            '/api/dms/quickbooks/quickbook-approvals?status=to_send&per_page=10&page=1&sort=created_at&search_term=clearing&_=1621970127182',
            [],
            [
                'access-token' => $this->accessToken()
            ]
        );

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertNotEmpty($responseJson);
        $this->assertIsArray($responseJson);

        $this->assertArrayHasKey('data', $responseJson);

        $this->assertArrayHasKey('id',$responseJson['data'][0]);
        $this->assertArrayHasKey('dealer_id',$responseJson['data'][0]);
        $this->assertArrayHasKey('qb_obj',$responseJson['data'][0]);
        $this->assertArrayHasKey('error_result',$responseJson['data'][0]);
        $this->assertArrayHasKey('tb_name',$responseJson['data'][0]);
        $this->assertArrayHasKey('tb_primary_id',$responseJson['data'][0]);
        $this->assertArrayHasKey('tb_label',$responseJson['data'][0]);
        $this->assertArrayHasKey('action_type',$responseJson['data'][0]);
        $this->assertArrayHasKey('created_at',$responseJson['data'][0]);
        $this->assertArrayHasKey('customer_name',$responseJson['data'][0]);
        $this->assertArrayHasKey('payment_method',$responseJson['data'][0]);
        $this->assertArrayHasKey('sales_ticket_num',$responseJson['data'][0]);
        $this->assertArrayHasKey('ticket_total',$responseJson['data'][0]);
        $this->assertArrayHasKey('qbo_account',$responseJson['data'][0]);
        $this->assertArrayHasKey('removed_by',$responseJson['data'][0]);
        $this->assertArrayHasKey('deleted_at',$responseJson['data'][0]);
    }

    /**
     * @covers ::index
     *
     * @group quickbook
     * @group DMS
     * @group DMS_QUICKBOOK
     */
    public function testIndexWithRemovedStatus() {
        $response = $this->json(
            'GET',
            '/api/dms/quickbooks/quickbook-approvals?status=removed&per_page=10&page=1&sort=created_at&_=1621975079636',
            [],
            [
                'access-token' => $this->accessToken()
            ]
        );

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertNotEmpty($responseJson);
        $this->assertIsArray($responseJson);

        $this->assertArrayHasKey('data', $responseJson);

        $this->assertArrayHasKey('id',$responseJson['data'][0]);
        $this->assertArrayHasKey('dealer_id',$responseJson['data'][0]);
        $this->assertArrayHasKey('qb_obj',$responseJson['data'][0]);
        $this->assertArrayHasKey('error_result',$responseJson['data'][0]);
        $this->assertArrayHasKey('tb_name',$responseJson['data'][0]);
        $this->assertArrayHasKey('tb_primary_id',$responseJson['data'][0]);
        $this->assertArrayHasKey('tb_label',$responseJson['data'][0]);
        $this->assertArrayHasKey('action_type',$responseJson['data'][0]);
        $this->assertArrayHasKey('created_at',$responseJson['data'][0]);
        $this->assertArrayHasKey('customer_name',$responseJson['data'][0]);
        $this->assertArrayHasKey('payment_method',$responseJson['data'][0]);
        $this->assertArrayHasKey('sales_ticket_num',$responseJson['data'][0]);
        $this->assertArrayHasKey('ticket_total',$responseJson['data'][0]);
        $this->assertArrayHasKey('qbo_account',$responseJson['data'][0]);

        // Only in removed
        $this->assertArrayHasKey('removed_by',$responseJson['data'][0]);
        $this->assertArrayHasKey('deleted_at',$responseJson['data'][0]);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->qbaSeed = new QuickbookApprovalSeeder();
        $this->qbaSeed->seed();

        $this->qbaDelSeed = new QuickbookApprovalDeletedSeeder();
        $this->qbaDelSeed->seed();
    }

    public function tearDown(): void
    {
        $this->qbaSeed->cleanUp();
        $this->qbaDelSeed->cleanUp();

        parent::tearDown();
    }

    /**
     * @covers ::destroy
     *
     * @group quickbook
     * @group DMS
     * @group DMS_QUICKBOOK
     */
    public function testDestroy()
    {
        $request = new DeleteQuickbookApprovalRequest(
            [
                'id' => $this->qbaSeed->qbApproval->id,
                'dealer_id' => $this->qbaSeed->qbApproval->dealer_id,
            ]
        );
        $controller = app()->make(QuickbookApprovalController::class);

        $controller->destroy($this->qbaSeed->qbApproval->id, $request);

        self::assertDatabaseMissing('quickbook_approval', ['id' => $this->qbaSeed->qbApproval->id]);
        self::assertDatabaseHas('quickbook_approval_deleted', ['id' => $this->qbaSeed->qbApproval->id]);
    }

    /**
     * @dataProvider noDealerSupportedInRequest
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     *
     * @covers ::destroy
     *
     * @group quickbook
     * @group DMS
     * @group DMS_QUICKBOOK
     */
    public function testDestroyWithoutDealerId(array $params,
                                               string $expectedException,
                                               string $expectedExceptionMessage,
                                               ?string $firstExpectedErrorMessage)
    {
        $request = new DeleteQuickbookApprovalRequest(
            [
                'id' => $this->qbaSeed->qbApproval->id,
            ]
        );
        $controller = app()->make(QuickbookApprovalController::class);


        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            $controller->destroy($this->qbaSeed->qbApproval->id, $request);
        } catch (ResourceException $exception) {
            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());

            throw $exception;
        }
    }

    /**
     * @covers ::moveStatus
     *
     * @group quickbook
     * @group DMS
     * @group DMS_QUICKBOOK
     */
    public function testMoveStatus()
    {
        $controller = app()->make(QuickbookApprovalController::class);

        $controller->moveStatus($this->qbaDelSeed->qbApprovalDeleted->id, 'to_send');

        self::assertDatabaseHas('quickbook_approval', ['id' => $this->qbaDelSeed->qbApprovalDeleted->id]);
        self::assertDatabaseMissing('quickbook_approval_deleted', ['id' => $this->qbaDelSeed->qbApprovalDeleted->id]);
    }

    /**
     * Examples of no dealer supported in request.
     *
     * @return array<string, array>
     * @throws \Exception when Uuid::uuid4 cannot generate a uuid
     */
    public function noDealerSupportedInRequest(): array
    {
        return [  // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No dealer' => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
        ];
    }
}
