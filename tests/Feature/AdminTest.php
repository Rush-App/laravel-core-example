<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RushApp\Core\Models\Language;

class AdminTest extends BaseAdminTest
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('languages')->truncate();
        Language::create(['name' => 'en']);
    }

    /**
     * all admin tests by correct token (getAdminInfoAfterRegister, updateAdminInfo, logout)
     * @test
     * @return void
     */
    public function allAdminTestsByCorrectToken()
    {
        $this->setAdminToken();
        $this->setAdminId();

        $this->getAdminInfoAfterRegister();
        $this->updateAdminInfo();
        $this->logout();

        $this->deleteAdmin();
    }

    /**
     * Get admin info
     * PARAMS: null
     * @return void
     */
    public function getAdminInfoAfterRegister()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->getJson('admin/user');

        $response->assertStatus(200)->assertJsonStructure([
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * update admin info when admin updated all data
     * PARAMS: $data
     * @return void
     */
    public function updateAdminInfo()
    {
        $data = [
            'name' => 'Alex',
            'email' => $this->email,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->putJson('admin/user/'.$this->adminId, $data);

        $response->assertStatus(200)->assertJsonFragment($data);
    }

    /**
     * change password
     * PARAMS: $data
     * @return void
     */
    public function changePassword()
    {
        $data = [
            'old_password' => '88888888',
            'password' => '999999999',
            'password_confirmation' => '999999999',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->postJson('admin/change-password', $data);

        $response->assertStatus(200)->assertJsonStructure([
            'token',
        ]);
    }

    /**
     * logout
     * @return void
     */
    public function logout()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->postJson('admin/logout');

        $response->assertStatus(200)->assertJsonStructure([
            'message',
        ]);
    }
}
