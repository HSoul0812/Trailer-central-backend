<?php

namespace Tests\Integration\Jobs\CRM\Email;

use App\Exceptions\PropertyDoesNotExists;
use App\Jobs\CRM\Email\ScrapeRepliesJob;
use App\Services\CRM\Email\ImapServiceInterface;
use App\Services\CRM\Email\ScrapeRepliesService;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Microsoft\OfficeServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Microsoft\Graph\Model\Message;
use Webklex\PHPIMAP\Message as ImapMessage;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Tests\database\seeds\CRM\Email\ScrapeRepliesSeeder;
use Tests\TestCase;
use Webklex\PHPIMAP\Support\MessageCollection;

/**
 * Class ScrapeRepliesJobTest
 * @package Tests\Integration\Jobs\CRM\Email
 *
 * @coversDefaultClass \App\Jobs\CRM\Email\ScrapeRepliesJob
 */
class ScrapeRepliesJobTest extends TestCase
{
    private const PARSED_EMAIL_ID_1 = PHP_INT_MAX;
    private const PARSED_EMAIL_ID_2 = PHP_INT_MAX - 1;
    private const PARSED_EMAIL_ID_3 = PHP_INT_MAX - 2;

    /**
     * @var LegacyMockInterface|MockInterface|GmailServiceInterface
     */
    protected $gmailService;

    /**
     * @var LegacyMockInterface|MockInterface|OfficeServiceInterface
     */
    protected $officeService;

    /**
     * @var LegacyMockInterface|MockInterface|ImapServiceInterface
     */
    protected $imapService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instanceMock('gmailService', GmailServiceInterface::class);
        $this->instanceMock('officeService', OfficeServiceInterface::class);
        $this->instanceMock('imapService', ImapServiceInterface::class);
    }

    /**
     * @group CRM
     * @covers ::handle
     *
     * @dataProvider dataProvider
     */
    public function testHandleGmail(array $emailIds, array $parsedEmails)
    {
        $seeder = new ScrapeRepliesSeeder();

        $seeder->seed();

        $salesPerson = $seeder->salesPeople[0];

        foreach ($salesPerson->email_folders as $emailFolder) {
            $this->gmailService
                ->shouldReceive('messages')
                ->with(
                    Mockery::on(function ($accessToken) use($salesPerson) {
                        return $accessToken->id === $salesPerson->tokens->first()->id;
                    }),
                    $emailFolder->name,
                    Mockery::on(function ($params) use ($emailFolder) {
                        return isset($params['after']) && $params['after'] === Carbon::parse($emailFolder->date_imported)->subDay()->isoFormat('YYYY/M/D');
                    })
                )
                ->once()
                ->andReturn($emailIds);

            for ($i = 0; $i < count($emailIds); $i++) {
                $this->gmailService
                    ->shouldReceive('message')
                    ->with($emailIds[$i])
                    ->once()
                    ->andReturn($parsedEmails[$i]);
            }
        }

        /** @var ScrapeRepliesJob $scrapeRepliesJob */
        $scrapeRepliesJob = new ScrapeRepliesJob($seeder->newDealerUser, $salesPerson);
        $scrapeRepliesService = $this->app->make(ScrapeRepliesService::class);

        $scrapeRepliesJob->handle($scrapeRepliesService);

        foreach ($parsedEmails as $email) {
            $this->assertDatabaseHas('crm_email_processed', [
                'user_id' => $salesPerson->user_id,
                'message_id' => $email->messageId
            ]);
        }

        $salesPerson->refresh();
        foreach ($salesPerson->email_folders as $emailFolder) {
            $this->assertNotNull($emailFolder->date_imported);
            $this->assertNotFalse(strtotime($emailFolder->date_imported));
        }

        $seeder->cleanUp();
    }

    /**
     * @group CRM
     * @covers ::handle
     *
     * @dataProvider dataProvider
     */
    public function testHandleOffice(array $emailIds, array $parsedEmails)
    {
        $microsoftMessages = new Collection([
            Mockery::instanceMock(Message::class),
            Mockery::instanceMock(Message::class),
            Mockery::instanceMock(Message::class),
        ]);

        $seeder = new ScrapeRepliesSeeder('office365');

        $seeder->seed();

        $salesPerson = $seeder->salesPeople[0];

        foreach ($salesPerson->email_folders as $emailFolder) {
            $this->officeService
                ->shouldReceive('messages')
                ->with(
                    Mockery::on(function ($accessToken) use($salesPerson) {
                        return $accessToken->id === $salesPerson->tokens->first()->id;
                    }),
                    $emailFolder->name,
                    Mockery::on(function ($params) use ($emailFolder) {
                        return isset($params[0]) && $params[0] === 'SentDateTime ge ' . Carbon::parse($emailFolder->date_imported)->subDay()->isoFormat('YYYY-MM-DD');
                    })
                )
                ->once()
                ->andReturn($microsoftMessages);

            for ($i = 0; $i < count($microsoftMessages); $i++) {
                $this->officeService
                    ->shouldReceive('message')
                    ->with($microsoftMessages[$i])
                    ->once()
                    ->andReturn($parsedEmails[$i]);
            }
        }

        /** @var ScrapeRepliesJob $scrapeRepliesJob */
        $scrapeRepliesJob = new ScrapeRepliesJob($seeder->newDealerUser, $salesPerson);
        $scrapeRepliesService = $this->app->make(ScrapeRepliesService::class);

        $scrapeRepliesJob->handle($scrapeRepliesService);

        foreach ($parsedEmails as $email) {
            $this->assertDatabaseHas('crm_email_processed', [
                'user_id' => $salesPerson->user_id,
                'message_id' => $email->messageId
            ]);
        }

        $salesPerson->refresh();
        foreach ($salesPerson->email_folders as $emailFolder) {
            $this->assertNotNull($emailFolder->date_imported);
            $this->assertNotFalse(strtotime($emailFolder->date_imported));
        }

        $seeder->cleanUp();
    }

    /**
     * @group CRM
     * @covers ::handle
     *
     * @dataProvider dataProvider
     */
    public function testHandleImap(array $emailIds, array $parsedEmails)
    {
        $imapMessages = new MessageCollection([
            Mockery::instanceMock(ImapMessage::class),
            Mockery::instanceMock(ImapMessage::class),
            Mockery::instanceMock(ImapMessage::class),
        ]);

        $seeder = new ScrapeRepliesSeeder('imap');

        $seeder->seed();

        $salesPerson = $seeder->salesPeople[0];

        foreach ($salesPerson->email_folders as $emailFolder) {
            $this->imapService
                ->shouldReceive('messages')
                ->once()
                ->andReturn($imapMessages);

            for ($i = 0; $i < count($emailIds); $i++) {
                $this->imapService
                    ->shouldReceive('overview')
                    ->once()
                    ->andReturn($parsedEmails[$i]);
            }
        }

        /** @var ScrapeRepliesJob $scrapeRepliesJob */
        $scrapeRepliesJob = new ScrapeRepliesJob($seeder->newDealerUser, $salesPerson);
        $scrapeRepliesService = $this->app->make(ScrapeRepliesService::class);

        $scrapeRepliesJob->handle($scrapeRepliesService);

        foreach ($parsedEmails as $email) {
            $this->assertDatabaseHas('crm_email_processed', [
                'user_id' => $salesPerson->user_id,
                'message_id' => $email->messageId
            ]);
        }

        $salesPerson->refresh();
        foreach ($salesPerson->email_folders as $emailFolder) {
            $this->assertNotNull($emailFolder->date_imported);
            $this->assertNotFalse(strtotime($emailFolder->date_imported));
        }

        $seeder->cleanUp();
    }

    /**
     * @return array
     * @throws PropertyDoesNotExists
     */
    public function dataProvider(): array
    {
        $emailIds = [
            self::PARSED_EMAIL_ID_1,
            self::PARSED_EMAIL_ID_2,
            self::PARSED_EMAIL_ID_3,
        ];

        $parsedEmails = [
            new ParsedEmail([
                'id' => self::PARSED_EMAIL_ID_1,
            ]),
            new ParsedEmail([
                'id' => self::PARSED_EMAIL_ID_2,
            ]),
            new ParsedEmail([
                'id' => self::PARSED_EMAIL_ID_3,
            ]),
        ];

        return [[$emailIds, $parsedEmails]];
    }
}
