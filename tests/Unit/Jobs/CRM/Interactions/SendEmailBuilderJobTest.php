<?php

namespace Tests\Unit\Jobs\CRM\Interactions;

use App\Exceptions\CRM\Email\Builder\SendEmailBuilderJobFailedException;;
use App\Models\CRM\Interactions\EmailHistory;
use App\Jobs\CRM\Interactions\SendEmailBuilderJob;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

/**
 * Test for App\Jobs\CRM\Leads\AutoAssignJob
 *
 * Class AutoAssignJobTest
 * @package Tests\Unit\Jobs\Files
 *
 * @coversDefaultClass \App\Jobs\CRM\Interactions\SendEmailBuilderJob
 */
class SendEmailBuilderJobTest extends TestCase
{
    /**
     * @var LegacyMockInterface|EmailBuilderServiceInterface
     */
    private $emailBuilderServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->emailBuilderServiceMock = Mockery::mock(EmailBuilderServiceInterface::class);
        $this->app->instance(EmailBuilderServiceInterface::class, $this->emailBuilderServiceMock);
    }


    /**
     * @group CRM
     * @covers ::handle
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testHandle()
    {
        // Mock EmailHistory
        $email = $this->getEloquentMock(EmailHistory::class);
        $email->email_id = 1;

        // Mock BuilderEmail
        $builder = $this->getEloquentMock(BuilderEmail::class);
        $builder->id = 1;
        $builder->type = 'campaign';
        $builder->leadId = 1;

        // Mock ParsedEmail
        $parsedEmail = $this->getEloquentMock(ParsedEmail::class);


        // Mock Email Builder
        $builder->shouldReceive('getLogParams')
            ->twice()
            ->andReturn([
                'lead' => $builder->leadId,
                'type' => $builder->type,
                $builder->type => $builder->id
            ]);

        // Mock Email Builder Save to DB
        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->withArgs([$builder])
            ->once()
            ->andReturn($email);

        // Mock Email Builder Send Email
        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->withArgs([$builder, $email->email_id])
            ->once()
            ->andReturn($parsedEmail);

        // Mock Email Builder Send Email
        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->withArgs([$builder, $parsedEmail])
            ->once();


        // Initialize Send Email Builder Job
        $sendEmailBuilderJob = new SendEmailBuilderJob($builder);

        // Handle Send Email Builder Job
        $result = $sendEmailBuilderJob->handle($this->emailBuilderServiceMock);

        // Receive Handling Auto Assign on Leads
        Log::shouldReceive('info')->with('Email Builder Mailed Successfully', [
            'lead' => $builder->leadId,
            'type' => $builder->type,
            $builder->type => $builder->id
        ]);

        // Assert True
        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::handle
     * @group EmailBuilder
     *
     * @throws BindingResolutionException
     */
    public function testHandleWithException()
    {
        // Mock EmailHistory
        $email = $this->getEloquentMock(EmailHistory::class);
        $email->email_id = 1;

        // Mock BuilderEmail
        $builder = $this->getEloquentMock(BuilderEmail::class);
        $builder->id = 1;
        $builder->type = 'campaign';
        $builder->leadId = 1;


        // Mock Email Builder
        $builder->shouldReceive('getLogParams')
            ->once()
            ->andReturn([
                'lead' => $builder->leadId,
                'type' => $builder->type,
                $builder->type => $builder->id
            ]);

        // Mock Email Builder Save to DB
        $this->emailBuilderServiceMock
            ->shouldReceive('saveToDb')
            ->withArgs([$builder])
            ->once()
            ->andReturn($email);

        // Mock Email Builder Send Email Failed
        $this->emailBuilderServiceMock
            ->shouldReceive('sendEmail')
            ->withArgs([$builder, $email->email_id])
            ->once();

        // Mock Email Builder Send Email
        $this->emailBuilderServiceMock
            ->shouldReceive('markSent')
            ->withArgs([$builder])
            ->once();

        // Expect Exception
        $this->expectException(SendEmailBuilderJobFailedException::class);


        // Initialize Send Email Builder Job
        $sendEmailBuilderJob = new SendEmailBuilderJob($builder);

        // Handle Send Email Builder Job
        $result = $sendEmailBuilderJob->handle($this->emailBuilderServiceMock);

        // Receive Handling Error on Send Email Builder Job
        Log::shouldReceive('error');

        // Assert True
        $this->assertFalse($result);
    }
}
