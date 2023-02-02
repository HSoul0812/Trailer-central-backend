<?php

namespace Tests\Unit\Services\CRM\Text;

use App\Exceptions\CRM\Text\NoDealerSmsNumberAvailableException;
use App\Exceptions\CRM\Text\NoLeadSmsNumberAvailableException;
use App\Exceptions\CRM\Text\ReplyInvalidArgumentException;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Text\Number;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\CRM\Text\TextService;
use App\Services\CRM\Text\TextServiceInterface;
use App\Services\CRM\Text\TwilioServiceInterface;
use App\Services\File\DTOs\FileDto;
use App\Services\File\FileService;
use App\Services\File\FileServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as DbCollection;
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
    private const TEST_FROM_NUMBER = '6543210986';
    private const TEST_FROM_NUMBER_2 = '1597537493';
    private const TEST_DEALER_ID = 1;
    private const TEST_PREFERRED_LOCATION = 2;
    private const TEST_LEAD_IDENTIFIER = 3;
    private const TEST_MESSAGE = 'some_message';
    private const TEST_TEXT_LOG_ID = 4;

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

    /**
     * @var NumberRepositoryInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $numberRepositoryMock;

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

        $this->numberRepositoryMock = Mockery::mock(NumberRepositoryInterface::class);
        $this->app->instance(NumberRepositoryInterface::class, $this->numberRepositoryMock);
    }

    /**
     * @group CRM
     * @covers ::send
     * @dataProvider sendParamsProvider
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

        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumberByCustomerNumber')
            ->once()
            ->with(self::TEST_TO_NUMBER, self::TEST_DEALER_ID)
            ->andReturn(null);

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

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->withNoArgs();

        $this->twilioServiceMock
            ->shouldReceive('send')
            ->once()
            ->with(self::TEST_FROM_NUMBER, self::TEST_TO_NUMBER, self::TEST_MESSAGE, self::TEST_FULL_NAME, [], self::TEST_DEALER_ID);

        $this->twilioServiceMock
            ->shouldReceive('getIsNumberInvalid')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with(Mockery::on(function($params) {
                return isset($params['lead_id']) && $params['lead_id'] === self::TEST_LEAD_IDENTIFIER && isset($params['status'])
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

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->withNoArgs();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->send(self::TEST_LEAD_IDENTIFIER, self::TEST_MESSAGE);

        $this->assertEquals($textLogMock, $result);
    }

    /**
     * @group CRM
     * @covers ::send
     * @dataProvider sendParamsProvider
     *
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param NewDealerUser|Mockery\MockInterface|Mockery\LegacyMockInterface $newDealerUser
     * @param CrmUser|Mockery\MockInterface|Mockery\LegacyMockInterface $crmUser
     * @return void
     */
    public function testSendWithActiveNumber($lead, $newDealerUser, $crmUser)
    {
        /** @var Number $activeNumber */
        $activeNumber = $this->getEloquentMock(Number::class);
        $activeNumber->dealer_number = 99999999;

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

        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumberByCustomerNumber')
            ->once()
            ->with(self::TEST_TO_NUMBER, self::TEST_DEALER_ID)
            ->andReturn($activeNumber);

        $this->dealerLocationRepositoryMock
            ->shouldReceive('findDealerNumber')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->withNoArgs();

        $this->twilioServiceMock
            ->shouldReceive('send')
            ->once()
            ->with($activeNumber->dealer_number, self::TEST_TO_NUMBER, self::TEST_MESSAGE, self::TEST_FULL_NAME, [], self::TEST_DEALER_ID);

        $this->twilioServiceMock
            ->shouldReceive('getIsNumberInvalid')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with(Mockery::on(function($params) {
                return isset($params['lead_id']) && $params['lead_id'] === self::TEST_LEAD_IDENTIFIER
                    && isset($params['status']) && isset($params['next_contact_date']) && strtotime($params['next_contact_date']);
            }));

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with([
                'lead_id'     => self::TEST_LEAD_IDENTIFIER,
                'from_number' => $activeNumber->dealer_number,
                'to_number'   => self::TEST_TO_NUMBER,
                'log_message' => self::TEST_MESSAGE,
                'files'       => []
            ])
            ->andReturn($textLogMock);

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->withNoArgs();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->send(self::TEST_LEAD_IDENTIFIER, self::TEST_MESSAGE);

        $this->assertEquals($textLogMock, $result);
    }

    /**
     * @group CRM
     * @covers ::send
     * @dataProvider sendParamsProvider
     *
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param NewDealerUser|Mockery\MockInterface|Mockery\LegacyMockInterface $newDealerUser
     * @param CrmUser|Mockery\MockInterface|Mockery\LegacyMockInterface $crmUser
     * @return void
     */
    public function testSendWithInvalidActiveNumber($lead, $newDealerUser, $crmUser)
    {
        /** @var Number $activeNumber */
        $activeNumber = $this->getEloquentMock(Number::class);
        $activeNumber->dealer_number = 99999999;
        $activeNumber->id = 11111111;

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

        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumberByCustomerNumber')
            ->once()
            ->with(self::TEST_TO_NUMBER, self::TEST_DEALER_ID)
            ->andReturn($activeNumber);

        $this->dealerLocationRepositoryMock
            ->shouldReceive('findDealerNumber')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->withNoArgs();

        $this->twilioServiceMock
            ->shouldReceive('send')
            ->once()
            ->with($activeNumber->dealer_number, self::TEST_TO_NUMBER, self::TEST_MESSAGE, self::TEST_FULL_NAME, [], self::TEST_DEALER_ID);

        $this->twilioServiceMock
            ->shouldReceive('getIsNumberInvalid')
            ->once()
            ->withNoArgs()
            ->andReturn(true);

        $this->numberRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(['id' => $activeNumber->id]);

        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with(Mockery::on(function($params) {
                return isset($params['lead_id']) && $params['lead_id'] === self::TEST_LEAD_IDENTIFIER
                    && isset($params['status']) && isset($params['next_contact_date']) && strtotime($params['next_contact_date']);
            }));

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with([
                'lead_id'     => self::TEST_LEAD_IDENTIFIER,
                'from_number' => $activeNumber->dealer_number,
                'to_number'   => self::TEST_TO_NUMBER,
                'log_message' => self::TEST_MESSAGE,
                'files'       => []
            ])
            ->andReturn($textLogMock);

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->withNoArgs();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->send(self::TEST_LEAD_IDENTIFIER, self::TEST_MESSAGE);

        $this->assertEquals($textLogMock, $result);
    }

    /**
     * @group CRM
     * @covers ::send
     * @dataProvider sendParamsProvider
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

        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumberByCustomerNumber')
            ->once()
            ->with(self::TEST_TO_NUMBER, self::TEST_DEALER_ID)
            ->andReturn(null);

        $this->dealerLocationRepositoryMock
            ->shouldReceive('findDealerNumber')
            ->once()
            ->with(self::TEST_DEALER_ID, self::TEST_PREFERRED_LOCATION)
            ->andReturn(self::TEST_FROM_NUMBER);

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->once()
            ->with($mediaUrl, self::TEST_DEALER_ID)
            ->andReturn($fileDtos);

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->withNoArgs();

        $this->twilioServiceMock
            ->shouldReceive('send')
            ->once()
            ->with(self::TEST_FROM_NUMBER, self::TEST_TO_NUMBER, self::TEST_MESSAGE, self::TEST_FULL_NAME, [$url1, $url2], self::TEST_DEALER_ID);

        $this->twilioServiceMock
            ->shouldReceive('getIsNumberInvalid')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with(Mockery::on(function($params) {
                return isset($params['lead_id']) && $params['lead_id'] === self::TEST_LEAD_IDENTIFIER
                    && isset($params['status']) && isset($params['next_contact_date']) && strtotime($params['next_contact_date']);
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

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once()
            ->withNoArgs();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->send(self::TEST_LEAD_IDENTIFIER, self::TEST_MESSAGE, $mediaUrl);

        $this->assertEquals($textLogMock, $result);
    }

    /**
     * @group CRM
     * @covers ::send
     * @dataProvider sendParamsProvider
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
            ->andReturn('');

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
     * @dataProvider sendParamsProvider
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

        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumberByCustomerNumber')
            ->once()
            ->with(self::TEST_TO_NUMBER, self::TEST_DEALER_ID)
            ->andReturn(null);

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
     * @group CRM
     * @covers ::send
     * @dataProvider sendParamsProvider
     *
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param NewDealerUser|Mockery\MockInterface|Mockery\LegacyMockInterface $newDealerUser
     * @param CrmUser|Mockery\MockInterface|Mockery\LegacyMockInterface $crmUser
     * @return void
     */
    public function testSendWithError($lead, $newDealerUser, $crmUser)
    {
        $exception = new \Exception();

        $this->expectException(\Exception::class);

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

        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumberByCustomerNumber')
            ->once()
            ->with(self::TEST_TO_NUMBER, self::TEST_DEALER_ID)
            ->andReturn(null);

        $this->dealerLocationRepositoryMock
            ->shouldReceive('findDealerNumber')
            ->once()
            ->with(self::TEST_DEALER_ID, self::TEST_PREFERRED_LOCATION)
            ->andReturn(self::TEST_FROM_NUMBER);

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->withNoArgs();

        $this->twilioServiceMock
            ->shouldReceive('send')
            ->once()
            ->with(self::TEST_FROM_NUMBER, self::TEST_TO_NUMBER, self::TEST_MESSAGE, self::TEST_FULL_NAME, [], self::TEST_DEALER_ID)
            ->andThrow($exception);

        $this->statusRepositoryMock
            ->shouldReceive('createOrUpdate')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->once()
            ->withNoArgs();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $service->send(self::TEST_LEAD_IDENTIFIER, self::TEST_MESSAGE);
    }

    /**
     * @group CRM
     * @covers ::reply
     * @dataProvider replyParamsProvider
     *
     * @param array $params
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param Number|Mockery\MockInterface|Mockery\LegacyMockInterface $activeNumber
     * @param DbCollection $textLogs
     * @return void
     */
    public function testReply(array $params, $lead, $activeNumber, DbCollection $textLogs)
    {
        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumber')
            ->with($params['To'], $params['From'])
            ->once()
            ->andReturn($activeNumber);

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->withNoArgs();

        $this->numberRepositoryMock
            ->shouldReceive('updateExpirationDate')
            ->once()
            ->with(Mockery::on(function($expirationDate) {
                return is_int($expirationDate);
            }), $params['To'], $activeNumber->dealer_number);

        $this->twilioServiceMock
            ->shouldReceive('sendViaTwilio')
            ->once()
            ->with($params['To'], $activeNumber->customer_number, $params['Body'], []);

        $this->twilioServiceMock
            ->shouldReceive('getIsNumberInvalid')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $this->textRepositoryMock
            ->shouldReceive('findByFromNumberToNumber')
            ->once()
            ->with($activeNumber->customer_number, $params['From'])
            ->andReturn($textLogs);

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->with(Mockery::on(function($creatParams) use ($activeNumber, $params) {
                return isset($creatParams['from_number']) && $creatParams['from_number'] === $params['From']
                    && isset($creatParams['to_number']) && $creatParams['to_number'] === $activeNumber->customer_number
                    && isset($creatParams['log_message']) && $creatParams['log_message'] === $params['Body']
                    && isset($creatParams['date_sent']) && strtotime($creatParams['date_sent'])
                    && isset($creatParams['files']) && $creatParams['files'] === [];
            }))
            ->once();

        $this->textRepositoryMock
            ->shouldReceive('stop')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->reply($params);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::reply
     * @dataProvider replyParamsProvider
     *
     * @param array $params
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param Number|Mockery\MockInterface|Mockery\LegacyMockInterface $activeNumber
     * @param DbCollection $textLogs
     * @return void
     */
    public function testReplyWithInvalidNumber(array $params, $lead, $activeNumber, DbCollection $textLogs)
    {
        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumber')
            ->with($params['To'], $params['From'])
            ->once()
            ->andReturn($activeNumber);

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->withNoArgs();

        $this->twilioServiceMock
            ->shouldReceive('sendViaTwilio')
            ->once()
            ->with($params['To'], $activeNumber->customer_number, $params['Body'], []);

        $this->twilioServiceMock
            ->shouldReceive('getIsNumberInvalid')
            ->once()
            ->withNoArgs()
            ->andReturn(true);

        $this->numberRepositoryMock
            ->shouldReceive('updateExpirationDate')
            ->never();

        $this->numberRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(['id' => $activeNumber->id])
            ->andReturn($textLogs);

        $this->textRepositoryMock
            ->shouldReceive('findByFromNumberToNumber')
            ->once()
            ->with($activeNumber->customer_number, $params['From'])
            ->andReturn($textLogs);

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->with(Mockery::on(function($creatParams) use ($activeNumber, $params) {
                return isset($creatParams['from_number']) && $creatParams['from_number'] === $params['From']
                    && isset($creatParams['to_number']) && $creatParams['to_number'] === $activeNumber->customer_number
                    && isset($creatParams['log_message']) && $creatParams['log_message'] === $params['Body']
                    && isset($creatParams['date_sent']) && strtotime($creatParams['date_sent'])
                    && isset($creatParams['files']) && $creatParams['files'] === [];
            }))
            ->once();

        $this->textRepositoryMock
            ->shouldReceive('stop')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->reply($params);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::reply
     * @dataProvider replyParamsProvider
     *
     * @param array $params
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param Number|Mockery\MockInterface|Mockery\LegacyMockInterface $activeNumber
     * @param DbCollection $textLogs
     * @return void
     */
    public function testReplyWithFiles(array $params, $lead, $activeNumber, DbCollection $textLogs)
    {
        $params['MediaUrl0'] = 'media_url1';
        $params['MediaUrl1'] = 'media_url2';

        $url1 = 'some_url1';
        $path1 = 'some_path1';
        $type1 = 'some_type1';

        $url2 = 'some_url2';
        $path2 = 'some_path2';
        $type2 = 'some_type2';

        $expectedFilesArray = [
            ['path' => $path1, 'type' => $type1],
            ['path' => $path2, 'type' => $type2],
        ];

        $fileDtos = new Collection([
            new FileDto($path1, null, $type1, $url1),
            new FileDto($path2, null, $type2, $url2),
        ]);

        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumber')
            ->with($params['To'], $params['From'])
            ->once()
            ->andReturn($activeNumber);

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->once()
            ->with(['media_url1', 'media_url2'], 0)
            ->andReturn($fileDtos);

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->withNoArgs();

        $this->numberRepositoryMock
            ->shouldReceive('updateExpirationDate')
            ->once()
            ->with(Mockery::on(function($expirationDate) {
                return is_int($expirationDate);
            }), $params['To'], $activeNumber->dealer_number);

        $this->twilioServiceMock
            ->shouldReceive('sendViaTwilio')
            ->once()
            ->with($params['To'], $activeNumber->customer_number, $params['Body'], [$url1, $url2]);

        $this->twilioServiceMock
            ->shouldReceive('getIsNumberInvalid')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $this->textRepositoryMock
            ->shouldReceive('findByFromNumberToNumber')
            ->once()
            ->with($activeNumber->customer_number, $params['From'])
            ->andReturn($textLogs);

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->with(Mockery::on(function($creatParams) use ($activeNumber, $params, $expectedFilesArray) {
                return isset($creatParams['from_number']) && $creatParams['from_number'] === $params['From']
                    && isset($creatParams['to_number']) && $creatParams['to_number'] === $activeNumber->customer_number
                    && isset($creatParams['log_message']) && $creatParams['log_message'] === $params['Body']
                    && isset($creatParams['date_sent']) && strtotime($creatParams['date_sent'])
                    && isset($creatParams['files']) && $creatParams['files'] === $expectedFilesArray;
            }))
            ->once();

        $this->textRepositoryMock
            ->shouldReceive('stop')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->reply($params);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::reply
     * @dataProvider replyParamsProvider
     *
     * @param array $params
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param Number|Mockery\MockInterface|Mockery\LegacyMockInterface $activeNumber
     * @param DbCollection $textLogs
     * @return void
     */
    public function testReplyWithStop(array $params, $lead, $activeNumber, DbCollection $textLogs)
    {
        $params['Body'] = 'STOP';

        /** @var TextLog $textLogMock */
        $textLogMock = $this->getEloquentMock(TextLog::class);
        $textLogMock->id = self::TEST_TEXT_LOG_ID;

        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumber')
            ->with($params['To'], $params['From'])
            ->once()
            ->andReturn($activeNumber);

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->withNoArgs();

        $this->numberRepositoryMock
            ->shouldReceive('updateExpirationDate')
            ->once()
            ->with(Mockery::on(function($expirationDate) {
                return is_int($expirationDate);
            }), $params['To'], $activeNumber->dealer_number);

        $this->twilioServiceMock
            ->shouldReceive('sendViaTwilio')
            ->once()
            ->with($params['To'], $activeNumber->customer_number, $params['Body'], []);

        $this->twilioServiceMock
            ->shouldReceive('getIsNumberInvalid')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $this->textRepositoryMock
            ->shouldReceive('findByFromNumberToNumber')
            ->once()
            ->with($activeNumber->customer_number, $params['From'])
            ->andReturn($textLogs);

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->with(Mockery::on(function($creatParams) use ($activeNumber, $params) {
                return isset($creatParams['from_number']) && $creatParams['from_number'] === $params['From']
                    && isset($creatParams['to_number']) && $creatParams['to_number'] === $activeNumber->customer_number
                    && isset($creatParams['log_message']) && $creatParams['log_message'] === $params['Body']
                    && isset($creatParams['date_sent']) && strtotime($creatParams['date_sent'])
                    && isset($creatParams['files']) && $creatParams['files'] === [];
            }))
            ->once()
            ->andReturn($textLogMock);

        $this->textRepositoryMock
            ->shouldReceive('stop')
            ->once()
            ->with(Mockery::on(function($creatParams) use ($activeNumber, $params, $lead, $textLogMock) {
                return isset($creatParams['sms_number']) && $creatParams['sms_number'] === $params['From']
                    && isset($creatParams['lead_id']) && $creatParams['lead_id'] === $lead->identifier
                    && isset($creatParams['text_id']) && $creatParams['text_id'] === $textLogMock->id;
            }));

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once();

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->reply($params);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::reply
     * @dataProvider replyParamsProvider
     *
     * @param array $params
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param Number|Mockery\MockInterface|Mockery\LegacyMockInterface $activeNumber
     * @param DbCollection $textLogs
     * @return void
     */
    public function testReplyFromCustomer(array $params, $lead, $activeNumber, DbCollection $textLogs)
    {
        $activeNumber->dealer_number = self::TEST_FROM_NUMBER_2;
        $activeNumber->customer_number = self::TEST_FROM_NUMBER;

        $expectedMessages = "Sent From: " . $params['From'] . "\nCustomer Name: $activeNumber->customer_name\n\n" . $params['Body'];

        /** @var TextLog $textLogMock */
        $textLogMock = $this->getEloquentMock(TextLog::class);
        $textLogMock->id = self::TEST_TEXT_LOG_ID;

        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumber')
            ->with($params['To'], $params['From'])
            ->once()
            ->andReturn($activeNumber);

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->withNoArgs();

        $this->numberRepositoryMock
            ->shouldReceive('updateExpirationDate')
            ->once()
            ->with(Mockery::on(function($expirationDate) {
                return is_int($expirationDate);
            }), $params['To'], $activeNumber->dealer_number);

        $this->twilioServiceMock
            ->shouldReceive('sendViaTwilio')
            ->once()
            ->with($params['To'], $activeNumber->dealer_number, $expectedMessages, []);

        $this->twilioServiceMock
            ->shouldReceive('getIsNumberInvalid')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $this->textRepositoryMock
            ->shouldReceive('findByFromNumberToNumber')
            ->once()
            ->with($activeNumber->dealer_number, $params['From'])
            ->andReturn($textLogs);

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->with(Mockery::on(function($creatParams) use ($activeNumber, $params, $expectedMessages) {
                return isset($creatParams['from_number']) && $creatParams['from_number'] === $params['From']
                    && isset($creatParams['to_number']) && $creatParams['to_number'] === $activeNumber->dealer_number
                    && isset($creatParams['log_message']) && $creatParams['log_message'] === $expectedMessages
                    && isset($creatParams['date_sent']) && strtotime($creatParams['date_sent'])
                    && isset($creatParams['files']) && $creatParams['files'] === [];
            }))
            ->once()
            ->andReturn($textLogMock);

        $this->textRepositoryMock
            ->shouldReceive('stop')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once();

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->reply($params);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::reply
     * @dataProvider replyParamsProvider
     *
     * @param array $params
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param Number|Mockery\MockInterface|Mockery\LegacyMockInterface $activeNumber
     * @param DbCollection $textLogs
     * @return void
     */
    public function testReplyWithException(array $params, $lead, $activeNumber, DbCollection $textLogs)
    {
        $exception = new \Exception();

        $this->expectException(\Exception::class);

        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumber')
            ->with($params['To'], $params['From'])
            ->once()
            ->andReturn($activeNumber);

        $this->textRepositoryMock
            ->shouldReceive('findByFromNumberToNumber')
            ->andReturn($textLogs);

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once()
            ->withNoArgs();

        $this->twilioServiceMock
            ->shouldReceive('sendViaTwilio')
            ->once()
            ->with($params['To'], $activeNumber->customer_number, $params['Body'], [])
            ->andThrow($exception);

        $this->twilioServiceMock
            ->shouldReceive('getIsNumberInvalid')
            ->never();

        $this->numberRepositoryMock
            ->shouldReceive('updateExpirationDate')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('stop')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->once();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $result = $service->reply($params);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::reply
     * @dataProvider replyParamsProvider
     *
     * @param array $params
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param Number|Mockery\MockInterface|Mockery\LegacyMockInterface $activeNumber
     * @param TextLog|Mockery\MockInterface|Mockery\LegacyMockInterface $textLog
     * @return void
     */
    public function testReplyWithoutFrom(array $params, $lead, $activeNumber, $textLog)
    {
        $this->expectException(ReplyInvalidArgumentException::class);

        unset($params['From']);

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->never();

        $this->numberRepositoryMock
            ->shouldReceive('updateExpirationDate')
            ->never();

        $this->twilioServiceMock
            ->shouldReceive('sendViaTwilio')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('stop')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $service->reply($params);
    }

    /**
     * @group CRM
     * @covers ::reply
     * @dataProvider replyParamsProvider
     *
     * @param array $params
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param Number|Mockery\MockInterface|Mockery\LegacyMockInterface $activeNumber
     * @param TextLog|Mockery\MockInterface|Mockery\LegacyMockInterface $textLog
     * @return void
     */
    public function testReplyWithoutTo(array $params, $lead, $activeNumber, $textLog)
    {
        $this->expectException(ReplyInvalidArgumentException::class);

        unset($params['To']);

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->never();

        $this->numberRepositoryMock
            ->shouldReceive('updateExpirationDate')
            ->never();

        $this->twilioServiceMock
            ->shouldReceive('sendViaTwilio')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('stop')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $service->reply($params);
    }

    /**
     * @group CRM
     * @covers ::reply
     * @dataProvider replyParamsProvider
     *
     * @param array $params
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param Number|Mockery\MockInterface|Mockery\LegacyMockInterface $activeNumber
     * @param TextLog|Mockery\MockInterface|Mockery\LegacyMockInterface $textLog
     * @return void
     */
    public function testReplyWithoutBody(array $params, $lead, $activeNumber, $textLog)
    {
        $this->expectException(ReplyInvalidArgumentException::class);

        unset($params['Body']);

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->never();

        $this->numberRepositoryMock
            ->shouldReceive('updateExpirationDate')
            ->never();

        $this->twilioServiceMock
            ->shouldReceive('sendViaTwilio')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('stop')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $service->reply($params);
    }

    /**
     * @group CRM
     * @covers ::reply
     * @dataProvider replyParamsProvider
     *
     * @param array $params
     * @param Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead
     * @param Number|Mockery\MockInterface|Mockery\LegacyMockInterface $activeNumber
     * @param TextLog|Mockery\MockInterface|Mockery\LegacyMockInterface $textLog
     * @return void
     */
    public function testReplyWithoutActiveNumber(array $params, $lead, $activeNumber, $textLog)
    {
        $this->expectException(ReplyInvalidArgumentException::class);

        $this->numberRepositoryMock
            ->shouldReceive('activeTwilioNumber')
            ->with($params['To'], $params['From'])
            ->once()
            ->andReturn(null);

        $this->textRepositoryMock
            ->shouldReceive('beginTransaction')
            ->never();

        $this->numberRepositoryMock
            ->shouldReceive('updateExpirationDate')
            ->never();

        $this->twilioServiceMock
            ->shouldReceive('sendViaTwilio')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('create')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('stop')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('commitTransaction')
            ->never();

        $this->fileServiceMock
            ->shouldReceive('bulkUpload')
            ->never();

        $this->textRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->never();

        /** @var TextServiceInterface $service */
        $service = $this->app->make(TextServiceInterface::class);
        $this->prepareFileService($service);

        $service->reply($params);
    }

    /**
     * @return object[][][]
     */
    public function sendParamsProvider(): array
    {
        /** @var LeadStatus|Mockery\MockInterface|Mockery\LegacyMockInterface $leadStatus */
        $leadStatus = $this->getEloquentMock(LeadStatus::class);
        $leadStatus->status = LeadStatus::STATUS_HOT;

        /** @var Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead */
        $lead = $this->getEloquentMock(Lead::class);

        $lead->dealer_id = self::TEST_DEALER_ID;
        $lead->identifier = self::TEST_LEAD_IDENTIFIER;
        $lead->leadStatus = $leadStatus;

        /** @var NewDealerUser|Mockery\MockInterface|Mockery\LegacyMockInterface $newDealerUser */
        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);

        /** @var CrmUser|Mockery\MockInterface|Mockery\LegacyMockInterface $crmUser */
        $crmUser = $this->getEloquentMock(CrmUser::class);

        $newDealerUser->crmUser = $crmUser;

        return [[$lead, $newDealerUser, $crmUser]];
    }

    /**
     * @return object[][][]
     */
    public function replyParamsProvider(): array
    {
        $params = [
            'From' => self::TEST_FROM_NUMBER,
            'To' => self::TEST_TO_NUMBER,
            'Body' => self::TEST_MESSAGE
        ];

        /** @var Lead|Mockery\MockInterface|Mockery\LegacyMockInterface $lead */
        $lead = $this->getEloquentMock(Lead::class);

        $lead->dealer_id = self::TEST_DEALER_ID;
        $lead->identifier = self::TEST_LEAD_IDENTIFIER;

        /** @var Number|Mockery\MockInterface|Mockery\LegacyMockInterface $activeNumber */
        $activeNumber = $this->getEloquentMock(Number::class);

        $activeNumber->dealer_number = self::TEST_FROM_NUMBER;
        $activeNumber->customer_number = self::TEST_FROM_NUMBER_2;
        $activeNumber->customer_name = self::TEST_FULL_NAME;

        /** @var TextLog|Mockery\MockInterface|Mockery\LegacyMockInterface $textLog */
        $textLog = $this->getEloquentMock(TextLog::class);

        $textLog->lead = $lead;
        $textLog->lead_id = $lead->identifier;

        return [[$params, $lead, $activeNumber, new DbCollection([$textLog])]];
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
