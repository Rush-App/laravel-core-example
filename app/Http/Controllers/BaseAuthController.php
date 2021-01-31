<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Monolog\Logger;
use RushApp\Core\Controllers\BaseController;
use RushApp\Core\Models\CoreBaseModelTrait;
use RushApp\Core\Services\LoggingService;

abstract class BaseAuthController extends BaseController
{
    use CoreBaseModelTrait;

    protected string $guard;

    public function login(Request $request)
    {
        return $this->loginAttempt($request->only(['email', 'password']));
    }

    public function registerAttempt(Request $request)
    {
        if ($data = $this->modelFill($request->all(), User::class)) {
            return $this->loginAttempt($request->only(['email', 'password']));
        } else {
            LoggingService::authLogging(
                Config::get('system_messages.could_not_register.message'),
                Logger::CRITICAL
            );

            return $this->responseWithError(__('response_messages.error_500'), 500);
        }
    }

    public function registerAdminAttempt(Request $request)
    {
        if ($data = $this->modelFill($request->all(), Admin::class)) {
            return $this->loginAttempt($request->only(['email', 'password']));
        } else {
            LoggingService::authLogging(
                Config::get('system_messages.could_not_register.message'),
                Logger::CRITICAL
            );

            return $this->responseWithError(__('response_messages.error_500'), 500);
        }
    }

    protected function loginAttempt(array $credentials)
    {
        if (!$token = Auth::guard($this->guard)->attempt($credentials)) {
            LoggingService::authLogging(
                Config::get('system_messages.could_not_login.message') . $credentials['email'],
                Logger::INFO
            );

            return $this->responseWithError(__('response_messages.incorrect_login'), 403);
        }

        return $this->successResponse(['token' => $token]);
    }

    public function changePasswordAttempt($request) {
        $user = User::find(Auth::id());

        if (!$token = Auth::guard($this->guard)->attempt(['email' => $user->email, 'password' => $request->old_password])) {
            LoggingService::authLogging(
                Config::get('system_messages.could_not_change_password.message') . $user->email,
                Logger::INFO
            );

            return $this->responseWithError(__('response_messages.incorrect_change_password'), 403);
        }

        $user->password = $request->password;
        $user->save();

        return $this->loginAttempt(['email' => $user->email, 'password' => $request->password]);
    }

    public function refreshToken()
    {
        try {
            $token = Auth::guard($this->guard)->refresh();
        } catch (\Exception $e) {
            return $this->responseWithError(__('response_messages.token_has_been_blacklisted'), 401);
        }

        return $this->successResponse(['token' => $token]);
    }

    public function logout()
    {
        Auth::guard($this->guard)->logout();

        return $this->successResponse(['message' => __('response_messages.logout')]);
    }
}
