<?php

namespace Tests\Unit\App\Services\WebsiteUser;

use App\Models\WebsiteUser\WebsiteUser;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use App\Services\WebsiteUser\PasswordResetService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Common\TestCase;

class PasswordResetServiceTest extends TestCase
{
    private MockObject $websiteUserRepository;

    public function testForgetPassword()
    {
        $this->markTestSkipped("The code can't read password.reset route from Dingo API.");
        $service = $this->getConcreteService();
        $this->websiteUserRepository->expects($this->once())
            ->method('get')
            ->willReturn(new Collection([new WebsiteUser(['email' => 'test@test.com'])]));
        $service->forgetPassword('test@test.com', null);
    }

    public function testForgetPasswordWithNoUser()
    {
        $service = $this->getConcreteService();
        $this->websiteUserRepository->expects($this->once())
            ->method('get')
            ->willReturn(new Collection());
        $this->expectException(NotFoundHttpException::class);
        $service->forgetPassword('test@test.com', null);
    }

    private function getConcreteService(): PasswordResetService
    {
        $this->websiteUserRepository = $this->mockWebsiteUserRepository();

        return new PasswordResetService($this->websiteUserRepository);
    }

    private function mockWebsiteUserRepository(): MockObject
    {
        return $this->createMock(WebsiteUserRepositoryInterface::class);
    }
}
