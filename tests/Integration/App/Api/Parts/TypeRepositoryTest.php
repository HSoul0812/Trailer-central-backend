<?php

declare(strict_types=1);

namespace Tests\Integration\App\Api\Parts;

use App\Repositories\Parts\TypeRepository;
use App\Repositories\Parts\TypeRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\Common\IntegrationTestCase;

class TypeRepositoryTest extends IntegrationTestCase
{
    /**
     * Test that SUT is properly bound by the application.
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     * @note IntegrationTestCase
     */
    public function testIoCForTheRepositoryInterfaceIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(TypeRepository::class, $concreteRepository);
    }

    /**
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    protected function getConcreteRepository(): TypeRepositoryInterface
    {
        return $this->app->make(TypeRepositoryInterface::class);
    }
}
