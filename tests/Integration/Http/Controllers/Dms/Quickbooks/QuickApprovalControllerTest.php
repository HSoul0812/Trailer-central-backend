<?php
namespace Tests\Integration\Http\Controllers\Dms\Quickbooks;

use App\Http\Controllers\v1\Dms\Quickbooks\QuickbookApprovalController;
use App\Http\Requests\Dms\Quickbooks\DeleteQuickbookApprovalRequest;
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
     * @group quickbook
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
     * @group quickbook
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


    /**
     * @covers ::destroy
     * @group quickbook
     */
    public function testDestroy()
    {
        $this->qbaSeed = new QuickbookApprovalSeeder();
        $this->qbaSeed->seed();

        $request = new DeleteQuickbookApprovalRequest(
            [
                'id' => $this->qbaSeed->qbApproval->id,
                'dealer_id' => $this->qbaSeed->qbApproval->dealer_id,
            ]
        );
        $controller = app()->make(QuickbookApprovalController::class);

        $response = $controller->destroy($this->qbaSeed->qbApproval->id, $request);

        $this->qbaSeed->cleanUp();

        self::assertEquals($this->qbaSeed->qbApproval->id, $response->id);

    }

    /**
     * @covers ::moveStatus
     * @group quickbook
     */
    public function testMoveStatus()
    {
        $this->qbaDelSeed = new QuickbookApprovalDeletedSeeder();
        $this->qbaDelSeed->seed();

        $controller = app()->make(QuickbookApprovalController::class);

        $response = $controller->moveStatus($this->qbaDelSeed->qbApprovalDeleted->id, 'to_send');

        $this->qbaDelSeed->cleanUp();

        self::assertEquals($this->qbaDelSeed->qbApprovalDeleted->removed_by, $response->dealer_id);

    }
}
