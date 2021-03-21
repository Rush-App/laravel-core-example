<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class RegisterTest extends TestCase
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
}