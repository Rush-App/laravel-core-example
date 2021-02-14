<?php

namespace Tests\OldFeature;

use App\Models\Admin;
use Tests\TestCase;

class BaseAdminTest extends TestCase
{
    protected string $adminToken = '';
    protected ?int $adminId = null;
    protected string $email = 'testAdmin333@test.test';
    protected string $password = '7777777';

    /**
     * User registration
     */
    public function registration()
    {
        return $this->postJson('admin/register', [
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
        return $this->postJson('admin/login', [
            'email' => !empty($email) ? $email : $this->email,
            'password' => !empty($password) ? $password : $this->password,
        ]);
    }

    /**
     * set Admin token
     * @return void
     */
    public function setAdminToken() {
        $responseData = $this->registration()->json();
        if (array_key_exists('token', $responseData)) {
            $this->adminToken = $responseData['token'];
        }
    }

    /**
     * set Admin id
     * PARAMS: null
     * @return void
     */
    public function setAdminId() {
        $responseData = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->getJson('admin/user')->json();
        if (array_key_exists('id', $responseData)) {
            $this->adminId = $responseData['id'];
        }
    }

    /**
     * delete admin
     * PARAMS: null
     * @test
     */
    public function deleteAdmin()
    {
        $user = Admin::where('email', $this->email)->first();
        if ($user) {
            $user->delete();
        }

        return $this->assertDatabaseMissing('admins', [
            'email' => $this->email,
        ]);
    }
}
