<?php
namespace Tests\Unit\Repositories\Website;

use App\Models\Website\DealerWebsiteUser;
use App\Models\Website\DealerWebsiteUserToken;
use App\Repositories\Website\WebsiteUserRepositoryInterface;
use Mockery;
use Tests\TestCase;

/**
 * Class WebsiteUserRepositoryTest
 * @coversDefaultClass App\Repositories\Website\WebsiteUserRepository
 * @package Tests\Unit\Repositories\Website
 */
class WebsiteUserRepositoryTest extends TestCase
{
    private $websiteUserMock;
    private $websiteUserTokenMock;

    public function setUp(): void {
        parent::setUp();
        $this->websiteUserMock = $this->getEloquentMock(DealerWebsiteUser::class);
        $this->websiteUserTokenMock = $this->getEloquentMock(DealerWebsiteUserToken::class);

        $this->app->instance(DealerWebsiteUser::class, $this->websiteUserMock);
        $this->app->instance(DealerWebsiteUserToken::class, $this->websiteUserTokenMock);
    }

    public function testGet(): void {
        $params = [
            'email' => 'email@email.com',
            'website_id' => 123
        ];

        $query = Mockery::mock(\StdClass::class);
        $this->websiteUserMock->shouldReceive('select')
        ->once()
        ->andReturn($query);

        $query
            ->shouldReceive('where')
            ->with('email', $params['email'])
            ->once()
            ->andReturnSelf();

        $query
            ->shouldReceive('where')
            ->with('website_id', $params['website_id'])
            ->once()
            ->andReturnSelf();

        $query
            ->shouldReceive('first')
            ->once()
            ->andReturn($this->websiteUserMock);

        $this->websiteUserMock->email = $params['email'];
        $this->websiteUserMock->website_id = $params['website_id'];

        $websiteUserRepository = $this->app->make(WebsiteUserRepositoryInterface::class);
        $user = $websiteUserRepository->get([
            'email' => 'email@email.com',
            'website_id' => 123
        ]);
        $this->assertEquals($user->email, $params['email']);
        $this->assertEquals($user->website_id, $params['website_id']);
    }

    public function testCreate(): void {
        $params = [
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'email' => 'email@email.com',
            'password' => '12345',
            'website_id' => 123,
            'token' => 'token12345'
        ];
        $hasOne = Mockery::mock(\StdClass::class);

        $this->websiteUserMock
            ->shouldReceive('setPasswordAttribute')
            ->once()
            ->passthru();

        $this->websiteUserMock
            ->shouldReceive('create')
            ->once()
            ->andReturnSelf();

        $this
            ->websiteUserMock
            ->shouldReceive('token')
            ->once()
            ->andReturn($hasOne);

        $hasOne
            ->shouldReceive('create')
            ->once()
            ->andReturn(['access_token' => $params['token']]);

        $this->websiteUserTokenMock->access_token = $params['token'];
        $this->websiteUserMock->first_name = $params['first_name'];
        $this->websiteUserMock->middle_name = $params['middle_name'];
        $this->websiteUserMock->last_name = $params['last_name'];
        $this->websiteUserMock->email = $params['email'];
        $this->websiteUserMock->password = $params['password'];
        $this->websiteUserMock->website_id = $params['website_id'];
        $this->websiteUserMock->token = $this->websiteUserTokenMock;

        $websiteUserRepository = $this->app->make(WebsiteUserRepositoryInterface::class);

        $user = $websiteUserRepository->create($params);

        $this->assertEquals($user->email, $params['email']);
        $this->assertEquals($user->website_id, $params['website_id']);
        $this->assertEquals($user->token->access_token, $params['token']);
    }
}
