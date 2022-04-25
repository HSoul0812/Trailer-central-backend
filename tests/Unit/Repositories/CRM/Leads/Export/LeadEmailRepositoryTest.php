<?php

namespace Tests\Unit\Repositories\CRM\Leads\Export;

use App\Models\CRM\Dms\Settings;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\Export\LeadEmail;
use Mockery;
use Tests\TestCase;

class LeadEmailRepositoryTest extends TestCase
{       
    /**
     * @var App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface
     */
    protected $leadEmailRepository;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->leadEmailRepository = Mockery::mock(LeadEmailRepositoryInterface::class);
        $this->app->instance(LeadEmailRepositoryInterface::class, $this->leadEmailRepository);
    }

    /**
     * @group CRM
     */
    public function testGetLeadEmailByLeadReturnsLeadEmail()
    {
        $lead = $this->getEloquentMock(Lead::class);
        $leadEmail = $this->getEloquentMock(LeadEmail::class);
        
        $this->leadEmailRepository
            ->shouldReceive('getLeadEmailByLead')
            ->once()
            ->with($lead)
            ->andReturn($leadEmail);     
                
        $leadEmail = $this->leadEmailRepository->getLeadEmailByLead($lead);
        
        $this->assertInstanceOf(LeadEmail::class, $leadEmail);
    }
    
    public function tearDown(): void
    {
        Mockery::close();
    }
    
}
