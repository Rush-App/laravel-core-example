<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Requests\Admin\UserRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use RushApp\Core\Controllers\BaseController;

class AdminController extends BaseController
{
    /**
     * the name of the model must be indicated in each controller
     * @var string
     */
    protected string $modelClassController = Admin::class;
    protected string $requestClassController = UserRequest::class;
    protected Admin $userModel;

    public function __construct(Admin $userModel)
    {
        $this->userModel = $userModel;
        parent::__construct();
    }

    public function getOne(Request $request)
    {
        $result = $this->userModel->getPersonalData($request);

        return $result['error'] === true
            ? $this->responseWithError($result['message'], $result['code'])
            : $this->successResponse($result['data']);
    }
}
