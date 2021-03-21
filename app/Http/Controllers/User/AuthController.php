<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use RushApp\Core\Controllers\BaseAuthController;

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
}
