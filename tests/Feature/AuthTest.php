<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\BaseFeatureTest;

class AuthTest extends BaseFeatureTest
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function register()
    {
        $userData = User::factory()->raw();
        $userData = Arr::only($userData, ['name', 'email']);
        $userData['password'] = $userData['password_confirmation'] = 'password';

        $this->assertDatabaseCount('users', 0);

        $response = $this->postJson('/register', $userData);

        $response->assertOk();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseMissing('users', [
            'password' => 'password',
        ]);
    }

    /**
     * @test
     */
    public function login()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token'
            ]);
    }

    /**
     * @test
     */
    public function logout()
    {
        $user = User::factory()->create();
        $this->signIn($user);

        $this->postJson('/logout')->assertOk();
        $this->postJson('/logout')->assertStatus(401);
    }
}