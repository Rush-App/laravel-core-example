<?php

namespace Tests\Feature;

class UserTest extends BaseUserTest
{

    /**
     * all user tests by correct token (getUserInfoAfterRegister, updateUserInfo, changePassword, logout)
     * @test
     * @return void
     */
    public function allUserTestsByCorrectToken()
    {
        $this->setUserToken();
        $this->setUserId();

        $this->getUserInfoAfterRegister();
        $this->updateUserInfo();
        $this->changePassword();
        $this->logout();

        $this->deleteUser();
    }

    /**
     * Get user info
     * PARAMS: null
     * @return void
     */
    public function getUserInfoAfterRegister()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->getJson('/user');

        $response->assertStatus(200)->assertJsonStructure([
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * update user info when user updated all data
     * PARAMS: $data
     * @return void
     */
    public function updateUserInfo()
    {
        $data = [
            'name' => 'Alex',
            'email' => $this->email,
            'language_id' => 2,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->putJson('/user/'.$this->userId, $data);

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
            'Authorization' => 'Bearer '.$this->userToken,
        ])->postJson('/change-password', $data);

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
            'Authorization' => 'Bearer '.$this->userToken,
        ])->postJson('/logout');

        $response->assertStatus(200)->assertJsonStructure([
            'message',
        ]);
    }
}
