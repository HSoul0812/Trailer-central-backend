<?php

namespace Tests\Feature\CRM\Leads;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

class AutoAssignCommandTest extends TestCase
{
    /**
     * Test all auto assign dealers
     *
     * @return void
     */
    public function testAll()
    {
        // Fake Mail
        Mail::fake();

        // Call Leads Assign Command
        $this->artisan('leads:assign:auto')
            ->assertSuccessful()
            ->assertExitCode(0);
    }

    public function testOne()
    {
        $factory->define(App\User::class, function (Faker $faker) {
            return [
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),
            ];
        });
    }
}
