<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\BaseAuthController;
use App\Http\Requests\Admin\RegisterRequest;
use App\Models\Admin;
use Illuminate\Http\Request;

class AuthController extends BaseAuthController
{
    /**
     * the name of the model must be indicated in each controller
     * @var string
     */
    protected string $modelClassController = Admin::class;
    protected string $guard = 'admin';

    public function register(RegisterRequest $request)
    {
        return $this->registerAdminAttempt($request);
    }
}
