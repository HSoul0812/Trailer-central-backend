<?php

namespace Tests\Integration\Http\Controllers\CRM\Email;

use Illuminate\Support\Str;
use Tests\Integration\IntegrationTestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\CRM\Email\TemplateSeeder;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Models\User\NewDealerUser;

/**
 * Class TemplateControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Email
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Email\TemplateController
 */

class TemplateControllerTest extends IntegrationTestCase
{
    use DatabaseTransactions;
    
    /**
     * @var TemplateSeeder
     */
    private $seeder;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $templateFile = "templates/versafix-1/template-versafix-1.html";

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new TemplateSeeder();
        $this->seeder->seed();
        $this->accessToken = $this->seeder->dealer->access_token;

        // Fixing Invalid User Id
        $newDealerUserRepo = app(NewDealerUserRepositoryInterface::class);
        $newDealerUser = $newDealerUserRepo->create([
            'user_id' => $this->seeder->user->user_id,
            'salt' => md5((string)$this->seeder->user->user_id),
            'auto_import_hide' => 0,
            'auto_msrp' => 0
        ]);
        $this->seeder->dealer->newDealerUser()->save($newDealerUser);
    }

    public function tearDown(): void
    {
        NewDealerUser::destroy($this->seeder->dealer->dealer_id);

        $this->seeder->cleanUp();

        parent::tearDown();
    }

    public function testIndex()
    {
        $response = $this->json(
            'GET',
            '/api/user/emailbuilder/template',
            [],
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'key',
                        'name',
                        'created_at'
                    ]
                ],
                'meta' => [
                    'pagination' => [
                        'total',
                        'count',
                        'per_page',
                        'current_page',
                        'total_pages'
                    ]
                ]
            ]);

        $expectedData = [];
        foreach ($this->seeder->createdTemplates as $template) {
            $expectedData[] = [
                'id' => (int)$template->id,
                'user_id' => (int)$template->user_id,
                'name' => $template->name ?? $template->custom_template_name,
                'key' => $template->template_key,
                'created_at' => (string)$template->date,
            ];
        }
        $this->assertResponseDataEquals($response, $expectedData, false);
    }

    public function testShow()
    {
        $template = $this->seeder->createdTemplates[0];

        $response = $this->json(
            'GET',
            '/api/user/emailbuilder/template/' . $template->id,
            [],
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'key',
                    'name',
                    'created_at'
                ]
            ]);

        $expectedData = [
            'id' => (int)$template->id,
            'user_id' => (int)$template->user_id,
            'name' => $template->name ?? $template->custom_template_name,
            'key' => $template->template_key,
            'created_at' => (string)$template->date,
        ];

        $this->assertResponseDataEquals($response, $expectedData, false);
    }

    public function testCreate()
    {
        $rawTemplate = [
            'name' => 'Create Email Template Test',
            'template' => $this->templateFile,
            'template_key' => Str::random(7)
        ];

        $response = $this->json(
            'PUT',
            '/api/user/emailbuilder/template',
            $rawTemplate,
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'key',
                    'name',
                    'created_at'
                ],
            ]);

        $this->assertSame($this->seeder->dealer->newDealerUser->user_id, $response['data']['user_id'], "The user doesn't match");
        $this->assertSame('Create Email Template Test', $response['data']['name'], "The template's name doesn't match");
    }

    public function testUpdate()
    {
        $template = $this->seeder->createdTemplates[0];
        $updatedInfo = [
            "name" => "Updated Template",
        ];

        $response = $this->json(
            'POST',
            '/api/user/emailbuilder/template/' . $template->id,
            $updatedInfo,
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'key',
                    'name',
                    'created_at'
                ],
            ]);

        $this->assertSame($this->seeder->dealer->newDealerUser->user_id, $response['data']['user_id'], "The user doesn't match");
        $this->assertSame($updatedInfo['name'], $response['data']['name'], "The template's name doesn't match");
    }

    public function testDestroy()
    {
        $template = $this->seeder->createdTemplates[0];

        $response = $this->json(
            'DELETE',
            '/api/user/emailbuilder/template/' . $template->id,
            [],
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(204);
    }
}
