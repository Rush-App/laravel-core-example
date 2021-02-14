<?php

namespace Tests\OldFeature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RushApp\Core\Models\Language;
use Tests\OldFeature\BaseUserTest;

class UserAuthTest extends BaseUserTest
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('languages')->truncate();
        Language::create(['name' => 'en']);
    }

    /**
     * First register (unique email)
     * @test
     * @return void
     */
    public function firstRegisterTest()
    {
        $response = $this->registration();
        $response->assertStatus(200)->assertJson(['token' => true]);
    }

    /**
     * This email is used (test333@test.test)
     * @test
     * @return void
     */
    public function secondRegisterTest()
    {
        $response = $this->registration();
        $response->assertStatus(422);
    }

    /**
     * Login with existing credentials
     * PARAMS: email, password
     * @test
     * @return void
     */
    public function firstLoginTest()
    {
        $response = $this->postJson('/login', ['email' => $this->email, 'password' => $this->password]);
        $response->assertStatus(200)->assertJson(['token' => true]);
    }

    /**
     * Checking user exists
     * @test
     * @return void
     */
    public function isUserExistsTest()
    {
        $this->assertDatabaseHas('users', [
            'email' => $this->email,
        ]);

        $this->deleteUser();
    }

    /**
     * Login with nonexistent credentials
     * PARAMS: email, password
     * @test
     * @return void
     */
    public function secondLoginTest()
    {
        $response = $this->postJson('/login', ['email' => $this->email, 'password' => $this->password]);
        $response->assertStatus(403);
    }
}
