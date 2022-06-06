<?php

namespace Tests\Integration\Services\Website;

use App\Models\Website\Website;
use App\Services\Website\WebsiteUserService;
use App\Services\Website\WebsiteUserServiceInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WebsiteUserServiceTest extends TestCase
{
    /**
     * @var Website
     */
    private $website;

    /**
     * Test that SUT is properly bound by the application
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForWebsiteUserServiceInterfaceIsWorking(): void
    {
        $concreteService = $this->getConcreteService();

        self::assertInstanceOf(WebsiteUserService::class, $concreteService);
    }

    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function testItAddsTheLastLoginWhenAUserCreatesAnAccount()
    {
        $this->createTestData();

        $service = $this->getConcreteService();
        $timestamp = CarbonImmutable::now();

        Carbon::setTestNow($timestamp);

        $service->createUser([
            'first_name' => 'John',
            'middle_name' => 'Mane',
            'last_name' => 'Doe',
            'email' => 'john.mane.doe@example.com',
            'password' => 'password',
            'website_id' => $this->website->id
        ]);

        $this->assertDatabaseHas('website_user', [
            'last_login' => $timestamp
        ]);

        $this->destroyTestData();
    }

    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function testItAddsTheLastLoginWhenAUserLogsIn()
    {
        $this->createTestData();

        $service = $this->getConcreteService();

        $user = $service->createUser([
            'first_name' => 'John',
            'middle_name' => 'Mane',
            'last_name' => 'Doe',
            'email' => 'john.mane.doe@example.com',
            'password' => 'password',
            'website_id' => $this->website->id
        ]);

        $timestamp = CarbonImmutable::now()->addMinute();

        self::assertNotSame($user->last_login->timestamp, $timestamp->timestamp);

        Carbon::setTestNow($timestamp);

        $service->loginUser([
            'website_id' => $this->website->id,
            'email' => 'john.mane.doe@example.com',
            'password' => 'password'
        ]);

        $this->assertDatabaseHas('website_user', [
            'last_login' => $timestamp
        ]);

        $this->destroyTestData();
    }

    /**
     * @return WebsiteUserServiceInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteService(): WebsiteUserServiceInterface
    {
        return $this->app->make(WebsiteUserServiceInterface::class);
    }

    private function createTestData()
    {
        $this->website = factory(Website::class)->create();
    }

    private function destroyTestData()
    {
        $this->website->delete();
    }
}
