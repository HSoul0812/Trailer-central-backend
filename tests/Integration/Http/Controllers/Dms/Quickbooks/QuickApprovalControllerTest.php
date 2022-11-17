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
        $response = $this->getJson(
            '/api/dms/quickbooks/quickbook-approvals?status=to_send&per_page=10&page=1&sort=created_at&search_term=clearing&_=1621970127182',
            [
                'access-token' => $this->accessToken()
            ]
        );

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'dealer_id',
                        'qb_obj',
                        'error_result',
                        'tb_name',
                        'tb_primary_id',
                        'tb_label',
                        'action_type',
                        'created_at',
                        'customer_name',
                        'payment_method',
                        'sales_ticket_num',
                        'ticket_total',
                        'qbo_account',
                        'removed_by',
                        'deleted_at',
                    ]
                ]
            ]);
    }

    /**
     * @covers ::index
     *
     * @group quickbook
     * @group DMS
     * @group DMS_QUICKBOOK
     */
    public function testIndexWithRemovedStatus() {
        $response = $this->getJson(
            '/api/dms/quickbooks/quickbook-approvals?status=removed&per_page=10&page=1&sort=created_at&_=1621975079636',
            [
                'access-token' => $this->accessToken()
            ]
        );

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'dealer_id',
                        'qb_obj',
                        'error_result',
                        'tb_name',
                        'tb_primary_id',
                        'tb_label',
                        'action_type',
                        'created_at',
                        'customer_name',
                        'payment_method',
                        'sales_ticket_num',
                        'ticket_total',
                        'qbo_account',
                        'removed_by',
                        'deleted_at',
                    ]
                ]
            ]);
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
