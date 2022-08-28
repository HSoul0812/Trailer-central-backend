<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\CRM\Email;

use App\Models\CRM\Email\Template;
use App\Repositories\CRM\Email\TemplateRepository;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\database\seeds\CRM\Email\TemplateSeeder;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class TemplateRepositoryTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    /**
     * @var TemplateSeeder
     */
    private $seeder;

    /**
     * Test that SUT is properly bound by the application
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    public function testRepositoryInterfaceIsBound(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(TemplateRepository::class, $concreteRepository);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @covers TemplateRepository::get
     */
    public function testGet(): void
    {
        // Given I have a collection of leads
        $this->seeder->seed();

        // Given I have a collection of template entries
        $templates = $this->seeder->createdTemplates;

        // Get Template
        $template = reset($templates);

        // When I call get
        // Then I got a single template
        /** @var Template $emailTemplate */
        $emailTemplate = $this->getConcreteRepository()->get(['id' => $template->id]);

        // Get must be Template
        self::assertInstanceOf(Template::class, $template);

        // Template id matches
        self::assertSame($emailTemplate->id, $template->id);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers TemplateRepository::get
     */
    public function testGetWithException(): void {
        // When I call create with invalid parameters
        // Then I expect see that one exception have been thrown with a specific message
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [App\Models\CRM\Email\Template] 0');

        // When I call get
        // Then I got a single template
        /** @var Template $emailTemplate */
        $emailTemplate = $this->getConcreteRepository()->get(['id' => 0]);

        // Template id matches param id
        self::assertNull($emailTemplate);
    }


    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new TemplateSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return TemplateRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): TemplateRepositoryInterface
    {
        return $this->app->make(TemplateRepositoryInterface::class);
    }
}