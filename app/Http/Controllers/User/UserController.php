<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\User\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RushApp\Core\Controllers\BaseCrudController;

class UserController extends BaseCrudController
{
    /**
     * the name of the model must be indicated in each controller
     * @var string
     */
    protected string $modelClassController = User::class;
    protected string $requestClassController = UserRequest::class;
    protected User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
        parent::__construct();
    }

    public function getOne(Request $request)
    {
        $user = Auth::user();

        return $this->successResponse($user);
    }

    public function updateOne(Request $request)
    {
        if ($this->isValidateError($request)) {
            return $this->isValidateError($request);
        }

        return $this->userModel->updatePersonalData($request)
            ?: $this->responseWithError(__('response_messages.editing_unavailable'), 403);
    }
}
