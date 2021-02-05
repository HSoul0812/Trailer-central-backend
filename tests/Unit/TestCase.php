<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Facade;
use Illuminate\Foundation\Testing\Concerns;
use Tests\CreatesApplication;
use Throwable;
use Mockery;

/**
 * Provide access to permissible outside world (container and application)
 *
 * Since Laravel `Illuminate\Foundation\Testing\TestCase` provide access to DB, Console, Session, Request, Auth
 * and ExceptionHandling, which make the unit test cases prune to be little rigorous. So, this TestCase class aim
 * to developer to be strict with the unit test cases.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    use WithFaker;
    use CreatesApplication;
    use Concerns\InteractsWithContainer;
    use Concerns\MocksApplicationServices;

    /**
     * The Illuminate application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * @var bool Indicates if we have made it through the base setUp function.
     */
    private $setUpHasRun;

    /**
     * The callbacks that should be run after the application is created.
     *
     * @var array
     */
    protected $afterApplicationCreatedCallbacks = [];

    /**
     * The callbacks that should be run before the application is destroyed.
     *
     * @var array
     */
    protected $beforeApplicationDestroyedCallbacks = [];

    /**
     * The exception thrown while running an application destruction callback.
     *
     * @var Throwable
     */
    private $callbackException;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!$this->app) {
            $this->refreshApplication();
        }

        $this->setUpFaker();

        foreach ($this->afterApplicationCreatedCallbacks as $callback) {
            $callback();
        }

        Facade::clearResolvedInstances();

        Model::setEventDispatcher($this->app['events']);

        $this->setUpHasRun = true;
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     * @throws Throwable
     */
    protected function tearDown(): void
    {
        if ($this->app) {
            $this->callBeforeApplicationDestroyedCallbacks();

            $this->app->flush();

            $this->app = null;
        }

        $this->setUpHasRun = false;

        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        Mockery::close();

        $this->afterApplicationCreatedCallbacks = [];
        $this->beforeApplicationDestroyedCallbacks = [];

        Artisan::forgetBootstrappers();

        if ($this->callbackException) {
            throw $this->callbackException;
        }
    }

    /**
     * Refresh the application instance.
     *
     * @return void
     */
    protected function refreshApplication(): void
    {
        $this->app = $this->createApplication();
    }

    /**
     * Execute the application's pre-destruction callbacks.
     *
     * @return void
     */
    protected function callBeforeApplicationDestroyedCallbacks(): void
    {
        foreach ($this->beforeApplicationDestroyedCallbacks as $callback) {
            $this->tryCallable($callback);
        }
    }

    private function tryCallable(callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            if (!$this->callbackException) {
                $this->callbackException = $e;
            }
        }
    }
}
