<?php
namespace Tests\Integration\Http\Controllers\Dms\Quickbooks;

use Tests\TestCase;

class QuickApprovalControllerTest extends TestCase
{
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
}
