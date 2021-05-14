<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\CRM\Email;

use App\Models\CRM\Email\Template;
use App\Repositories\CRM\Email\TemplateRepository;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
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
     * @typeOfTest IntegrationTestCase
     * @dataProvider validGetParametersProvider
     *
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     * @covers TemplateRepository::get
     */
    public function testGet(array $params): void
    {
        // Given I have a collection of leads
        $this->seeder->seed();

        // Parse Values
        $values = $this->seeder->extractValues($params);

        // When I call get
        // Then I got a single template
        /** @var Template $template */
        $template = $this->getConcreteRepository()->get($values);

        // Get must be Template
        self::assertInstanceOf(Template::class, $template);

        // Template id matches param id
        self::assertSame($template->id, $values['id']);
    }


    /**
     * Examples of parameters with expected total.
     *
     * @return array[]
     */
    public function validGetParametersProvider(): array
    {
        $templateIdLambda = static function (TemplateSeeder $seeder) {
            return $seeder->createdTemplates[0]->getKey();
        };

        return [                 // array $parameters
            'By dummy template' => [['id' => $templateIdLambda]],
        ];
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