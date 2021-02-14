<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseAuthController;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;

class AuthController extends BaseAuthController
{
    /**
     * the name of the model must be indicated in each controller
     * @var string
     */
    protected string $modelClassController = User::class;
    protected string $guard = 'user';

    public function register(RegisterRequest $request)
    {
        return $this->registerAttempt($request);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->changePasswordAttempt($request);
    }
}
