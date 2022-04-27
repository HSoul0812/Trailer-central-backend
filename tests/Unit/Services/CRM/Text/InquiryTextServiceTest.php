<?php

namespace Tests\Unit\Services\CRM\Leads;

use App\Services\CRM\Text\TextServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use App\Services\CRM\Text\InquiryTextServiceInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use Mockery;
use Tests\TestCase;

/**
 * Test for App\Services\CRM\Text\InquiryTextService
 *
 * Class InquiryTextServiceTest
 * @package Tests\Unit\Services\CRM\Text
 *
 * @coversDefaultClass \App\Services\CRM\Text\InquiryTextService
 */
class InquiryTextServiceTest extends TestCase
{
    /**
     * @var LegacyMockInterface|TextServiceInterface
     */
    private $twillioServiceMock;

    /**
     * @var LegacyMockInterface|DealerLocationRepositoryInterface
     */
    private $dealerLocationRepositoryMock;

    private $sendParams;

    public function setUp(): void
    {
        parent::setUp();

        $this->twillioServiceMock = Mockery::mock(TextServiceInterface::class);

        $this->app->instance(TextServiceInterface::class, $this->twillioServiceMock);

        $this->dealerLocationRepositoryMock = Mockery::mock(DealerLocationRepositoryInterface::class);
        $this->app->instance(DealerLocationRepositoryInterface::class, $this->dealerLocationRepositoryMock);

        $this->sendParams = [
            "dealer_id" => 1004,
            "website_id" => 500,
            "dealer_location_id" => 14448,
            "inventory_id" => 2307377,
            "phone_number" => "0245366382",
            "sms_message" => "Hello world",
            "customer_name" => "Norris Oduro",
            "inventory_name" => "Teiko",
            "referral" => "/",
            "cookie_session_id" => null,
            "is_from_classifieds" => true
        ];
    }


    /**
     * @group CRM
     * @covers ::send
     *
     * @throws BindingResolutionException
     */
    public function testSend()
    {
        // Send Request Params
        $params = $this->sendParams;

        $phone = "1234567890";

        $this->dealerLocationRepositoryMock
            ->shouldReceive('findDealerNumber')
            ->once()
            ->with($params['dealer_id'], $params['dealer_location_id'])
            ->andReturn($phone);

        $messageBody = 'A customer has made an inquiry about model with stock #: ' . $params['inventory_name'] .
            "\nSent From: " . $params['phone_number'] . "\nCustomer Name: " . $params['customer_name'] . "\nUnit link: " . $params['referral'] . "\n\n" . $params['sms_message'];

        $this->twillioServiceMock
            ->shouldReceive('send')
            ->once()
            ->with($params['phone_number'], $phone, $messageBody, $params['customer_name']);

        $service = $this->app->make(InquiryTextServiceInterface::class);

        $service->send($params);
    }

    /**
     * @group CRM
     * @covers ::merge
     */
    public function testMerge()
    {
        $params = $this->sendParams;

        $this->assertArrayNotHasKey('first_name', $params);
        $this->assertArrayNotHasKey('last_name', $params);
        $this->assertArrayNotHasKey('title', $params);
        $this->assertArrayNotHasKey('comments', $params);

        $service = $this->app->make(InquiryTextServiceInterface::class);

        $params = $service->merge($params);

        $this->assertArrayHasKey('first_name', $params);
        $this->assertArrayHasKey('last_name', $params);
        $this->assertArrayHasKey('title', $params);
        $this->assertArrayHasKey('comments', $params);

        $this->assertEquals(['text'], $params['lead_types']);
        $this->assertEquals('Teiko', $params['title']);
        $this->assertEquals('phone', $params['preferred_contact']);
        $this->assertEquals('Hello world', $params['comments']);
    }
}
