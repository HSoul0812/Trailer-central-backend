<?php

namespace Tests\Unit\Services\CRM\Text;

use App\Exceptions\CRM\Text\NoDealerSmsNumberAvailableException;
use App\Exceptions\CRM\Text\NoLeadSmsNumberAvailableException;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Leads\Lead;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\CRM\Text\TextService;
use App\Services\CRM\Text\TextServiceInterface;
use App\Services\CRM\Text\TwilioServiceInterface;
use App\Services\File\DTOs\FileDto;
use App\Services\File\FileService;
use App\Services\File\FileServiceInterface;
use Illuminate\Support\Collection;
use Mockery;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Class TextServiceTest
 * @package Tests\Unit\Services\CRM\Text
 *
 * @coversDefaultClass \App\Services\CRM\Text\TextService
 */
class TextServiceTest extends TestCase
{
    private const TEST_FULL_NAME = 'test_full_name';
    private const TEST_TO_NUMBER = '123456';
    private const TEST_FROM_NUMBER = '654321';
    private const TEST_DEALER_ID = PHP_INT_MAX;
    private const TEST_PREFERRED_LOCATION = PHP_INT_MAX - 1;
    private const TEST_LEAD_IDENTIFIER = PHP_INT_MAX - 2;
    private const TEST_MESSAGE = 'some_message';

    /**
     * @var TwilioServiceInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $twilioServiceMock;

    /**
     * @var DealerLocationRepositoryInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $dealerLocationRepositoryMock;

    /**
     * @var StatusRepositoryInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $statusRepositoryMock;

    /**
     * @var TextRepositoryInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $textRepositoryMock;

    /**
     * @var FileServiceInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $fileServiceMock;

    /**
     * @var LeadRepositoryInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $leadRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->twilioServiceMock = Mockery::mock(TwilioServiceInterface::class);
        $this->app->instance(TwilioServiceInterface::class, $this->twilioServiceMock);

        $this->dealerLocationRepositoryMock = Mockery::mock(DealerLocationRepositoryInterface::class);
        $this->app->instance(DealerLocationRepositoryInterface::class, $this->dealerLocationRepositoryMock);

        $this->statusRepositoryMock = Mockery::mock(StatusRepositoryInterface::class);
        $this->app->instance(StatusRepositoryInterface::class, $this->statusRepositoryMock);

        $this->textRepositoryMock = Mockery::mock(TextRepositoryInterface::class);
        $this->app->instance(TextRepositoryInterface::class, $this->textRepositoryMock);

        $this->fileServiceMock = Mockery::mock(FileService::class);

        $this->leadRepositoryMock = Mockery::mock(LeadRepositoryInterface::class);
        $this->app->instance(LeadRepositoryInterface::class, $this->leadRepositoryMock);
    }

    /**
     * @group CRM
     * @covers ::send
     * @dataProvider leadParamsProvider
     *
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param NewDealerUser|Mockery\MockInterface|Mockery\LegacyMockInterface $newDealerUser
     * @param CrmUser|Mockery\MockInterface|Mockery\LegacyMockInterface $crmUser
     * @return void
     */
    public function testSend($lead, $newDealerUser, $crmUser)
    {
        $textLogMock = $this->getEloquentMock(TextLog::class);

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => self::TEST_LEAD_IDENTIFIER])
            ->andReturn($lead);

        $lead->shouldReceive('newDealerUser')
            ->once()
            ->withNoArgs()
            ->andReturn($newDealerUser);

        $newDealerUser->shouldReceive('first')
            ->once()
            ->withNoArgs()
            ->andReturn($newDealerUser);

        $crmUser->shouldReceive('getFullNameAttribute')
            ->once()
            ->andReturn(self::TEST_FULL_NAME);

        $lead->shouldReceive('getTextPhoneAttribute')
            ->once()
            ->andReturn(self::TEST_TO_NUMBER);

        $lead->shouldReceive('getPreferredLocationAttribute')
            ->once()
            ->andReturn(self::TEST_PREFERRED_LOCATION);

        $this->dealerLocationRepositoryMock
            ->shouldReceive('findDealerNumber')
            ->once()
            ->with(self::TEST_DEALER_ID, self::TEST_PREFERRED_LOCATION)
            ->andReturn(self::TEST_FROM_NUMBER);

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->twilioServiceMock
            ->shouldReceive('send')
            ->once()
            ->with(self::TEST_FROM_NUMBER, self::TEST_TO_NUMBER, self::TEST_MESSAGE, self::TEST_FULL_NAME, []);

        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with(Mockery::on(function($params) {
                return isset($params['lead_id']) && $params['lead_id'] === self::TEST_LEAD_IDENTIFIER
                    && isset($params['status']) && $params['status'] === Lead::STATUS_MEDIUM
                    && isset($params['next_contact_date']) && strtotime($params['next_contact_date']);
            }));

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with([
                'lead_id'     => self::TEST_LEAD_IDENTIFIER,
                'from_number' => self::TEST_FROM_NUMBER,
                'to_number'   => self::TEST_TO_NUMBER,
                'log_message' => self::TEST_MESSAGE,
                'files'       => []
            ])
            ->andReturn($textLogMock);

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->send(self::TEST_LEAD_IDENTIFIER, self::TEST_MESSAGE);

        $this->assertEquals($textLogMock, $result);
    }

    /**
     * @group CRM
     * @covers ::send
     * @dataProvider leadParamsProvider
     *
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param NewDealerUser|Mockery\MockInterface|Mockery\LegacyMockInterface $newDealerUser
     * @param CrmUser|Mockery\MockInterface|Mockery\LegacyMockInterface $crmUser
     * @return void
     */
    public function testSendWithMediaUrl($lead, $newDealerUser, $crmUser)
    {
        $textLogMock = $this->getEloquentMock(TextLog::class);

        $mediaUrl = ['media_url1', 'media_url2'];

        $url1 = 'some_url1';
        $path1 = 'some_path1';
        $type1 = 'some_type1';

        $url2 = 'some_url2';
        $path2 = 'some_path2';
        $type2 = 'some_type2';

        $fileDtos = new Collection([
            new FileDto($path1, null, $type1, $url1),
            new FileDto($path2, null, $type2, $url2),
        ]);

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => self::TEST_LEAD_IDENTIFIER])
            ->andReturn($lead);

        $lead->shouldReceive('newDealerUser')
            ->once()
            ->withNoArgs()
            ->andReturn($newDealerUser);

        $newDealerUser->shouldReceive('first')
            ->once()
            ->withNoArgs()
            ->andReturn($newDealerUser);

        $crmUser->shouldReceive('getFullNameAttribute')
            ->once()
            ->andReturn(self::TEST_FULL_NAME);

        $lead->shouldReceive('getTextPhoneAttribute')
            ->once()
            ->andReturn(self::TEST_TO_NUMBER);

        $lead->shouldReceive('getPreferredLocationAttribute')
            ->once()
            ->andReturn(self::TEST_PREFERRED_LOCATION);

        $this->dealerLocationRepositoryMock
            ->shouldReceive('findDealerNumber')
            ->once()
            ->with(self::TEST_DEALER_ID, self::TEST_PREFERRED_LOCATION)
            ->andReturn(self::TEST_FROM_NUMBER);

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->once()
            ->with($mediaUrl, self::TEST_DEALER_ID)
            ->andReturn($fileDtos);;

        $this->twilioServiceMock
            ->shouldReceive('send')
            ->once()
            ->with(self::TEST_FROM_NUMBER, self::TEST_TO_NUMBER, self::TEST_MESSAGE, self::TEST_FULL_NAME, [$url1, $url2]);

        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with(Mockery::on(function($params) {
                return isset($params['lead_id']) && $params['lead_id'] === self::TEST_LEAD_IDENTIFIER
                    && isset($params['status']) && $params['status'] === Lead::STATUS_MEDIUM
                    && isset($params['next_contact_date']) && strtotime($params['next_contact_date']);
            }));

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with([
                'lead_id'     => self::TEST_LEAD_IDENTIFIER,
                'from_number' => self::TEST_FROM_NUMBER,
                'to_number'   => self::TEST_TO_NUMBER,
                'log_message' => self::TEST_MESSAGE,
                'files'       => [
                    [
                        'path' => $path1,
                        'type' => $type1
                    ],
                    [
                        'path' => $path2,
                        'type' => $type2
                    ],
                ]
            ])
            ->andReturn($textLogMock);

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->send(self::TEST_LEAD_IDENTIFIER, self::TEST_MESSAGE, $mediaUrl);

        $this->assertEquals($textLogMock, $result);
    }

    /**
     * @group CRM
     * @covers ::send
     * @dataProvider leadParamsProvider
     *
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param NewDealerUser|Mockery\MockInterface|Mockery\LegacyMockInterface $newDealerUser
     * @param CrmUser|Mockery\MockInterface|Mockery\LegacyMockInterface $crmUser
     * @return void
     */
    public function testSendWithoutToNumber($lead, $newDealerUser, $crmUser)
    {
        $this->expectException(NoLeadSmsNumberAvailableException::class);

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => self::TEST_LEAD_IDENTIFIER])
            ->andReturn($lead);

        $lead->shouldReceive('newDealerUser')
            ->once()
            ->withNoArgs()
            ->andReturn($newDealerUser);

        $newDealerUser->shouldReceive('first')
            ->once()
            ->withNoArgs()
            ->andReturn($newDealerUser);

        $crmUser->shouldReceive('getFullNameAttribute')
            ->once()
            ->andReturn(self::TEST_FULL_NAME);

        $lead->shouldReceive('getTextPhoneAttribute')
            ->once()
            ->andReturn(null);

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->twilioServiceMock
            ->shouldReceive('send')
            ->never();

        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $service->send(self::TEST_LEAD_IDENTIFIER, self::TEST_MESSAGE);
    }

    /**
     * @group CRM
     * @covers ::send
     * @dataProvider leadParamsProvider
     *
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param NewDealerUser|Mockery\MockInterface|Mockery\LegacyMockInterface $newDealerUser
     * @param CrmUser|Mockery\MockInterface|Mockery\LegacyMockInterface $crmUser
     * @return void
     */
    public function testSendWithoutFromNumber($lead, $newDealerUser, $crmUser)
    {
        $this->expectException(NoDealerSmsNumberAvailableException::class);

        $this->leadRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->with(['id' => self::TEST_LEAD_IDENTIFIER])
            ->andReturn($lead);

        $lead->shouldReceive('newDealerUser')
            ->once()
            ->withNoArgs()
            ->andReturn($newDealerUser);

        $newDealerUser->shouldReceive('first')
            ->once()
            ->withNoArgs()
            ->andReturn($newDealerUser);

        $crmUser->shouldReceive('getFullNameAttribute')
            ->once()
            ->andReturn(self::TEST_FULL_NAME);

        $lead->shouldReceive('getTextPhoneAttribute')
            ->once()
            ->andReturn(self::TEST_TO_NUMBER);

        $lead->shouldReceive('getPreferredLocationAttribute')
            ->once()
            ->andReturn(self::TEST_PREFERRED_LOCATION);

        $this->dealerLocationRepositoryMock
            ->shouldReceive('findDealerNumber')
            ->once()
            ->with(self::TEST_DEALER_ID, self::TEST_PREFERRED_LOCATION)
            ->andReturn(null);

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->twilioServiceMock
            ->shouldReceive('send')
            ->never();

        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $service->send(self::TEST_LEAD_IDENTIFIER, self::TEST_MESSAGE);
    }

    /**
     * @return object[][][]
     */
    public function leadParamsProvider(): array
    {
        /** @var Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead */
        $lead = $this->getEloquentMock(Lead::class);

        $lead->dealer_id = self::TEST_DEALER_ID;
        $lead->identifier = self::TEST_LEAD_IDENTIFIER;

        /** @var NewDealerUser|Mockery\MockInterface|Mockery\LegacyMockInterface $newDealerUser */
        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);

        /** @var CrmUser|Mockery\MockInterface|Mockery\LegacyMockInterface $crmUser */
        $crmUser = $this->getEloquentMock(CrmUser::class);

        $newDealerUser->crmUser = $crmUser;

        return [[$lead, $newDealerUser, $crmUser]];
    }

    /**
     * @param TextServiceInterface $textService
     * @return void
     */
    protected function prepareFileService(TextServiceInterface $textService)
    {
        $reflector = new ReflectionProperty(TextService::class, 'fileService');
        $reflector->setAccessible(true);
        $reflector->setValue($textService, $this->fileServiceMock);
    }
}
