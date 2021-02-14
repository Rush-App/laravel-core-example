<?php

namespace Tests\OldFeature;

use App\Models\User;
use Tests\TestCase;

class BaseUserTest extends TestCase
{
    protected string $userToken = '';
    protected ?int $userId = null;
    protected string $email = 'test333@test.test';
    protected string $password = '88888888';

    /**
     * User registration
     */
    public function registration()
    {
        return $this->postJson('/register', [
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ]);
    }

    /**
     * User login
     * @param string|null $email
     * @param string|null $password
     */
    public function login(string $email = null, string $password = null)
    {
        return $this->postJson('/login', [
            'email' => !empty($email) ? $email : $this->email,
            'password' => !empty($password) ? $password : $this->password,
        ]);
    }

    /**
     * set User token
     * @return void
     */
    public function setUserToken() {
        $responseData = $this->registration()->json();
        if (array_key_exists('token', $responseData)) {
            $this->userToken = $responseData['token'];
        }
    }

    /**
     * set User id
     * PARAMS: null
     * @return void
     */
    public function setUserId() {
        $responseData = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->getJson('/user')->json();
        if (array_key_exists('id', $responseData)) {
            $this->userId = $responseData['id'];
        }
    }

    /**
     * delete user
     * PARAMS: null
     * @test
     */
    public function deleteUser()
    {
        $user = User::where('email', $this->email)->first();
        if ($user) {
            $user->delete();
        }

        return $this->assertDatabaseMissing('users', [
            'email' => $this->email,
        ]);
    }
}
