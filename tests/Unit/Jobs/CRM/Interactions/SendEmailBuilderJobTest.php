<?php

namespace Tests\Unit\Jobs\CRM\Interactions;

use App\Exceptions\CRM\Email\Builder\SendEmailBuilderJobFailedException;;

use App\Exceptions\PropertyDoesNotExists;
use App\Jobs\CRM\Interactions\SendEmailBuilderJob;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * Test for App\Jobs\CRM\Interactions\SendEmailBuilderJob
 *
 * Class SendEmailBuilderJobTest
 * @package ests\Unit\Jobs\CRM\Interactions
 *
 * @coversDefaultClass \App\Jobs\CRM\Interactions\SendEmailBuilderJob
 */
class SendEmailBuilderJobTest extends TestCase
{
    private const BUILDER_EMAIL_ID = PHP_INT_MAX;
    private const BUILDER_EMAIL_TYPE = 'campaign';
    private const BUILDER_EMAIL_LEAD_ID = PHP_INT_MAX - 1;
    private const BUILDER_EMAIL_LEAD_EMAIL_ID = PHP_INT_MAX - 2;

    private const PARSED_EMAIL_MESSAGE_ID = PHP_INT_MAX - 3;


    /**
     * @group CRM
     * @covers ::handle
     * @group EmailBuilder
     *
     * @dataProvider dataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param EmailBuilderServiceInterface|LegacyMockInterface $emailBuilderServiceMock
     */
    public function testHandle(
        BuilderEmail $builderEmail,
        ParsedEmail $parsedEmail,
        EmailBuilderServiceInterface $emailBuilderServiceMock
    ) {
        $emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->once()
            ->with($builderEmail)
            ->andReturn($parsedEmail);

        $emailBuilderServiceMock
            ->shouldReceive('markSentMessageId')
            ->once()
            ->with($builderEmail, $parsedEmail);

        $emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->once()
            ->with($parsedEmail);

        $sendEmailBuilderJob = new SendEmailBuilderJob($builderEmail);
        $result = $sendEmailBuilderJob->handle($emailBuilderServiceMock);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::handle
     * @group EmailBuilder
     *
     * @dataProvider dataProvider
     *
     * @param BuilderEmail $builderEmail
     * @param ParsedEmail $parsedEmail
     * @param EmailBuilderServiceInterface|LegacyMockInterface $emailBuilderServiceMock
     */
    public function testHandleWithException(
        BuilderEmail $builderEmail,
        ParsedEmail $parsedEmail,
        EmailBuilderServiceInterface $emailBuilderServiceMock
    ) {
        $emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->once()
            ->with($builderEmail)
            ->andReturn($parsedEmail);

        $emailBuilderServiceMock
            ->shouldReceive('markSentMessageId')
            ->once()
            ->with($builderEmail, $parsedEmail);

        $emailBuilderServiceMock
            ->shouldReceive('markEmailSent')
            ->once()
            ->with($parsedEmail)
            ->andThrow(\Exception::class);

        $this->expectException(SendEmailBuilderJobFailedException::class);

        $sendEmailBuilderJob = new SendEmailBuilderJob($builderEmail);
        $result = $sendEmailBuilderJob->handle($emailBuilderServiceMock);

        $this->assertTrue($result);
    }

    /**
     * @return array[]
     * @throws PropertyDoesNotExists
     */
    public function dataProvider(): array
    {
        $builderEmail = new BuilderEmail([
            'id' => self::BUILDER_EMAIL_ID,
            'type' => self::BUILDER_EMAIL_TYPE,
            'lead_id' => self::BUILDER_EMAIL_LEAD_ID,
            'email_id' => self::BUILDER_EMAIL_LEAD_EMAIL_ID,
        ]);

        $parsedEmail = new ParsedEmail([
            'message_id' => self::PARSED_EMAIL_MESSAGE_ID
        ]);

        /** @var EmailBuilderServiceInterface|LegacyMockInterface $builder */
        $emailBuilderServiceMock = Mockery::mock(EmailBuilderServiceInterface::class);

        return [[$builderEmail, $parsedEmail, $emailBuilderServiceMock]];
    }
}
